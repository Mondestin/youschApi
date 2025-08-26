<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherPerformance;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TeacherPerformanceRepository extends BaseRepository implements TeacherPerformanceRepositoryInterface
{
    public function __construct(TeacherPerformance $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated performances with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedPerformances(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['teacher', 'evaluator']);

        // Apply filters
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['evaluation_period'])) {
            $query->where('evaluation_period', 'like', '%' . $filters['evaluation_period'] . '%');
        }

        if (isset($filters['evaluator_id'])) {
            $query->where('evaluator_id', $filters['evaluator_id']);
        }

        if (isset($filters['rating_min'])) {
            $query->where('overall_rating', '>=', $filters['rating_min']);
        }

        if (isset($filters['rating_max'])) {
            $query->where('overall_rating', '<=', $filters['rating_max']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('evaluation_date', 'desc')->paginate($perPage);
    }

    /**
     * Get performance by ID
     *
     * @param int $id
     * @return TeacherPerformance|null
     */
    public function getPerformanceById(int $id): ?TeacherPerformance
    {
        return $this->model->with(['teacher', 'evaluator'])->find($id);
    }

    /**
     * Get performance by teacher and period
     *
     * @param int $teacherId
     * @param string $period
     * @return TeacherPerformance|null
     */
    public function getPerformanceByTeacherAndPeriod(int $teacherId, string $period): ?TeacherPerformance
    {
        return $this->model->with(['teacher', 'evaluator'])
            ->where('teacher_id', $teacherId)
            ->where('evaluation_period', $period)
            ->first();
    }

    /**
     * Create a new performance evaluation
     *
     * @param array $data
     * @return TeacherPerformance
     */
    public function createPerformance(array $data): TeacherPerformance
    {
        $data['status'] = $data['status'] ?? 'draft';
        $data['evaluation_date'] = $data['evaluation_date'] ?? now();
        
        return $this->model->create($data);
    }

    /**
     * Update performance evaluation
     *
     * @param TeacherPerformance $performance
     * @param array $data
     * @return bool
     */
    public function updatePerformance(TeacherPerformance $performance, array $data): bool
    {
        return $performance->update($data);
    }

    /**
     * Delete performance evaluation
     *
     * @param TeacherPerformance $performance
     * @return bool
     */
    public function deletePerformance(TeacherPerformance $performance): bool
    {
        return $performance->delete();
    }

    /**
     * Get performances by teacher
     *
     * @param int $teacherId
     * @return Collection
     */
    public function getPerformancesByTeacher(int $teacherId): Collection
    {
        return $this->model->with(['teacher', 'evaluator'])
            ->where('teacher_id', $teacherId)
            ->orderBy('evaluation_date', 'desc')
            ->get();
    }

    /**
     * Get performances by evaluator
     *
     * @param int $evaluatorId
     * @return Collection
     */
    public function getPerformancesByEvaluator(int $evaluatorId): Collection
    {
        return $this->model->with(['teacher', 'evaluator'])
            ->where('evaluator_id', $evaluatorId)
            ->orderBy('evaluation_date', 'desc')
            ->get();
    }

    /**
     * Get performances by period
     *
     * @param string $period
     * @return Collection
     */
    public function getPerformancesByPeriod(string $period): Collection
    {
        return $this->model->with(['teacher', 'evaluator'])
            ->where('evaluation_period', 'like', '%' . $period . '%')
            ->orderBy('evaluation_date', 'desc')
            ->get();
    }

    /**
     * Get performances by rating range
     *
     * @param float $minRating
     * @param float $maxRating
     * @return Collection
     */
    public function getPerformancesByRatingRange(float $minRating, float $maxRating): Collection
    {
        return $this->model->with(['teacher', 'evaluator'])
            ->whereBetween('overall_rating', [$minRating, $maxRating])
            ->orderBy('overall_rating', 'desc')
            ->get();
    }

    /**
     * Get performance statistics
     *
     * @return array
     */
    public function getPerformanceStatistics(): array
    {
        $totalEvaluations = $this->model->count();
        $draftEvaluations = $this->model->where('status', 'draft')->count();
        $publishedEvaluations = $this->model->where('status', 'published')->count();
        $archivedEvaluations = $this->model->where('status', 'archived')->count();

        $averageRating = $this->model->where('status', 'published')->avg('overall_rating') ?? 0;
        $highestRating = $this->model->where('status', 'published')->max('overall_rating') ?? 0;
        $lowestRating = $this->model->where('status', 'published')->min('overall_rating') ?? 0;

        $ratingDistribution = $this->model->where('status', 'published')
            ->selectRaw('ROUND(overall_rating) as rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        $monthlyStats = $this->model->selectRaw('MONTH(evaluation_date) as month, COUNT(*) as count')
            ->whereYear('evaluation_date', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'total_evaluations' => $totalEvaluations,
            'draft_evaluations' => $draftEvaluations,
            'published_evaluations' => $publishedEvaluations,
            'archived_evaluations' => $archivedEvaluations,
            'average_rating' => round($averageRating, 2),
            'highest_rating' => $highestRating,
            'lowest_rating' => $lowestRating,
            'rating_distribution' => $ratingDistribution,
            'monthly_stats' => $monthlyStats
        ];
    }

    /**
     * Get teacher performance trends
     *
     * @param int $teacherId
     * @return array
     */
    public function getTeacherPerformanceTrends(int $teacherId): array
    {
        $performances = $this->model->with(['teacher', 'evaluator'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->orderBy('evaluation_date', 'asc')
            ->get();

        $trends = [];
        foreach ($performances as $performance) {
            $trends[] = [
                'period' => $performance->evaluation_period,
                'date' => $performance->evaluation_date,
                'overall_rating' => $performance->overall_rating,
                'teaching_effectiveness' => $performance->teaching_effectiveness,
                'classroom_management' => $performance->classroom_management,
                'subject_knowledge' => $performance->subject_knowledge,
                'communication_skills' => $performance->communication_skills,
                'professional_development' => $performance->professional_development,
                'student_engagement' => $performance->student_engagement,
                'assessment_quality' => $performance->assessment_quality
            ];
        }

        return $trends;
    }
} 