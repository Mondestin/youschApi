<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\{ReportCard, StudentGPA};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReportCardRepository implements ReportCardRepositoryInterface
{
    public function getPaginatedReportCards(array $filters): LengthAwarePaginator
    {
        $query = ReportCard::with(['student', 'class', 'term', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        if (isset($filters['date_from'])) {
            $query->where('issued_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('issued_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('issued_date', 'desc')->paginate(15);
    }

    public function getAllReportCards(array $filters): Collection
    {
        $query = ReportCard::with(['student', 'class', 'term', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        if (isset($filters['date_from'])) {
            $query->where('issued_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('issued_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('issued_date', 'desc')->get();
    }

    public function getReportCardById(int $id): ?ReportCard
    {
        return ReportCard::with(['student', 'class', 'term', 'academicYear'])->find($id);
    }

    public function createReportCard(array $data): ReportCard
    {
        return ReportCard::create($data);
    }

    public function updateReportCard(ReportCard $reportCard, array $data): bool
    {
        return $reportCard->update($data);
    }

    public function deleteReportCard(ReportCard $reportCard): bool
    {
        return $reportCard->delete();
    }

    public function getReportCardsByStudent(int $studentId): Collection
    {
        return ReportCard::with(['class', 'term', 'academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('issued_date', 'desc')
            ->get();
    }

    public function getReportCardsByClass(int $classId): Collection
    {
        return ReportCard::with(['student', 'term', 'academicYear'])
            ->where('class_id', $classId)
            ->orderBy('issued_date', 'desc')
            ->get();
    }

    public function getReportCardsByTerm(int $termId): Collection
    {
        return ReportCard::with(['student', 'class', 'academicYear'])
            ->where('term_id', $termId)
            ->orderBy('issued_date', 'desc')
            ->get();
    }

    public function getReportCardsByAcademicYear(int $academicYearId): Collection
    {
        return ReportCard::with(['student', 'class', 'term'])
            ->where('academic_year_id', $academicYearId)
            ->orderBy('issued_date', 'desc')
            ->get();
    }

    public function getReportCardByStudentAndTerm(int $studentId, int $termId, int $academicYearId): ?ReportCard
    {
        return ReportCard::with(['student', 'class', 'term', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    public function generateReportCard(int $studentId, int $classId, int $termId, int $academicYearId): ReportCard
    {
        // Check if report card already exists
        $existingReportCard = ReportCard::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if ($existingReportCard) {
            return $existingReportCard;
        }

        // Calculate GPA and CGPA
        $gpa = $this->calculateGPA($studentId, $termId, $academicYearId);
        $cgpa = $this->calculateCGPA($studentId, $academicYearId);

        // Generate remarks
        $remarks = $this->generateRemarks($gpa);

        // Create new report card
        return ReportCard::create([
            'student_id' => $studentId,
            'class_id' => $classId,
            'term_id' => $termId,
            'academic_year_id' => $academicYearId,
            'gpa' => $gpa,
            'cgpa' => $cgpa,
            'remarks' => $remarks,
            'issued_date' => now(),
            'format' => 'Digital',
        ]);
    }

    public function generateClassReportCards(int $classId, int $termId, int $academicYearId): array
    {
        // Get all students in the class
        $students = \App\Models\Students\Student::where('class_id', $classId)->get();
        $generatedCards = [];

        foreach ($students as $student) {
            $reportCard = $this->generateReportCard($student->id, $classId, $termId, $academicYearId);
            $generatedCards[] = $reportCard;
        }

        return $generatedCards;
    }

    public function exportReportCardToPDF(int $reportCardId): string
    {
        // This is a placeholder implementation
        // In a real application, you would generate a PDF using a library like DomPDF or TCPDF
        $reportCard = ReportCard::with(['student', 'class', 'term', 'academicYear'])->find($reportCardId);
        
        if (!$reportCard) {
            throw new \Exception('Report card not found');
        }

        // For now, return a simple text representation
        return "Report Card for {$reportCard->student->name} - {$reportCard->class->name} - {$reportCard->term->name}";
    }

    public function getReportCardStatistics(array $filters = []): array
    {
        $query = ReportCard::query();

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        if (isset($filters['date_from'])) {
            $query->where('issued_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('issued_date', '<=', $filters['date_to']);
        }

        $totalReportCards = $query->count();
        $averageGPA = $query->avg('gpa');
        $averageCGPA = $query->avg('cgpa');
        $highestGPA = $query->max('gpa');
        $lowestGPA = $query->min('gpa');

        $formatDistribution = $query->selectRaw('format, COUNT(*) as count')
            ->groupBy('format')
            ->pluck('count', 'format')
            ->toArray();

        return [
            'total_report_cards' => $totalReportCards,
            'average_gpa' => round($averageGPA, 2),
            'average_cgpa' => round($averageCGPA, 2),
            'highest_gpa' => $highestGPA,
            'lowest_gpa' => $lowestGPA,
            'format_distribution' => $formatDistribution,
        ];
    }

    public function getReportCardTrends(int $studentId, int $academicYearId): array
    {
        $reportCards = ReportCard::with(['term'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('term_id')
            ->get();

        $trends = [];
        foreach ($reportCards as $reportCard) {
            $trends[] = [
                'term' => $reportCard->term->name,
                'gpa' => $reportCard->gpa,
                'cgpa' => $reportCard->cgpa,
                'issued_date' => $reportCard->issued_date,
            ];
        }

        return $trends;
    }

    private function calculateGPA(int $studentId, int $termId, int $academicYearId): float
    {
        $studentGPA = StudentGPA::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        return $studentGPA ? $studentGPA->gpa : 0.0;
    }

    private function calculateCGPA(int $studentId, int $academicYearId): float
    {
        $studentGPAs = StudentGPA::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        if ($studentGPAs->isEmpty()) {
            return 0.0;
        }

        $totalGPA = $studentGPAs->sum('gpa');
        $termCount = $studentGPAs->count();

        return round($totalGPA / $termCount, 2);
    }

    private function generateRemarks(float $gpa): string
    {
        if ($gpa >= 3.8) {
            return 'Outstanding performance! Keep up the excellent work.';
        } elseif ($gpa >= 3.0) {
            return 'Good performance. Continue to work hard and improve further.';
        } elseif ($gpa >= 2.0) {
            return 'Satisfactory performance. Focus on areas that need improvement.';
        } else {
            return 'Performance needs improvement. Please seek additional support and work harder.';
        }
    }
} 