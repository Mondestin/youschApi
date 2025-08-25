<?php

namespace App\Repositories\Students;

use App\Models\Students\Student;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedStudents(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['school', 'campus', 'classRoom']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['enrollment_date_from'])) {
            $query->whereDate('enrollment_date', '>=', $filters['enrollment_date_from']);
        }

        if (isset($filters['enrollment_date_to'])) {
            $query->whereDate('enrollment_date', '<=', $filters['enrollment_date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getStudentById(int $id, array $relationships = []): ?Student
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createStudent(array $data): Student
    {
        return $this->model->create($data);
    }

    public function updateStudent(Student $student, array $data): bool
    {
        return $student->update($data);
    }

    public function deleteStudent(Student $student): bool
    {
        return $student->delete();
    }

    public function changeStudentStatus(Student $student, string $status): bool
    {
        return $student->update(['status' => $status]);
    }

    public function assignStudentToClass(Student $student, int $classId): bool
    {
        return $student->update(['class_id' => $classId]);
    }

    public function getStudentsBySchool(int $schoolId): Collection
    {
        return $this->model->where('school_id', $schoolId)
            ->with(['campus', 'classRoom'])
            ->get();
    }

    public function getStudentsByCampus(int $campusId): Collection
    {
        return $this->model->where('campus_id', $campusId)
            ->with(['school', 'classRoom'])
            ->get();
    }

    public function getStudentsByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)
            ->with(['school', 'campus'])
            ->get();
    }

    public function getStudentsByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['school', 'campus', 'classRoom'])
            ->get();
    }

    public function searchStudents(string $searchTerm): Collection
    {
        return $this->model->where(function($query) use ($searchTerm) {
            $query->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_number', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('parent_name', 'like', "%{$searchTerm}%");
        })
        ->with(['school', 'campus', 'classRoom'])
        ->get();
    }

    public function getStudentStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        $total = $query->count();
        $active = $query->where('status', Student::STATUS_ACTIVE)->count();
        $graduated = $query->where('status', Student::STATUS_GRADUATED)->count();
        $transferred = $query->where('status', Student::STATUS_TRANSFERRED)->count();
        $suspended = $query->where('status', Student::STATUS_SUSPENDED)->count();
        $inactive = $query->where('status', Student::STATUS_INACTIVE)->count();

        $recent = $query->where('enrollment_date', '>=', now()->subDays(30))->count();

        $byGender = $query->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->get();

        $byClass = $query->selectRaw('class_id, COUNT(*) as count')
            ->whereNotNull('class_id')
            ->groupBy('class_id')
            ->get();

        return [
            'total_students' => $total,
            'active_students' => $active,
            'graduated_students' => $graduated,
            'transferred_students' => $transferred,
            'suspended_students' => $suspended,
            'inactive_students' => $inactive,
            'recent_enrollments' => $recent,
            'students_by_gender' => $byGender,
            'students_by_class' => $byClass,
        ];
    }

    public function hasRelatedRecords(Student $student): array
    {
        return [
            'has_enrollments' => $student->enrollments()->exists(),
            'has_grades' => $student->grades()->exists(),
            'has_academic_history' => $student->academicHistory()->exists(),
            'has_documents' => $student->documents()->exists(),
        ];
    }

    public function getStudentAcademicPerformance(Student $student): array
    {
        $academicHistory = $student->academicHistory()
            ->with(['subject', 'classRoom', 'term', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->get();

        $overallGPA = $academicHistory->avg('gpa');
        $totalSubjects = $academicHistory->count();
        $passingSubjects = $academicHistory->where('grade', '!=', 'F')->count();
        $failingSubjects = $academicHistory->where('grade', 'F')->count();

        return [
            'academic_history' => $academicHistory,
            'performance_summary' => [
                'overall_gpa' => round($overallGPA, 2),
                'total_subjects' => $totalSubjects,
                'passing_subjects' => $passingSubjects,
                'failing_subjects' => $failingSubjects,
                'pass_rate' => $totalSubjects > 0 ? round(($passingSubjects / $totalSubjects) * 100, 2) : 0,
            ]
        ];
    }

    public function isEmailRegistered(string $email, ?int $excludeStudentId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeStudentId) {
            $query->where('id', '!=', $excludeStudentId);
        }
        
        return $query->exists();
    }
} 