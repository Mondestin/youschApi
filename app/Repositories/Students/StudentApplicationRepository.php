<?php

namespace App\Repositories\Students;

use App\Models\Students\StudentApplication;
use App\Models\Students\Student;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentApplicationRepository extends BaseRepository implements StudentApplicationRepositoryInterface
{
    public function __construct(StudentApplication $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedApplications(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['school', 'campus', 'reviewer']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        if (isset($filters['reviewer_id'])) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('applied_on', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('applied_on', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('applied_on', 'desc')->paginate($perPage);
    }

    /**
     * Get all applications with filters (without pagination)
     */
    public function getAllApplications(array $filters): Collection
    {
        $query = $this->model->with(['school', 'campus', 'reviewer']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        if (isset($filters['reviewer_id'])) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('applied_on', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('applied_on', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('applied_on', 'desc')->get();
    }

    public function getApplicationById(int $id, array $relationships = []): ?StudentApplication
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createApplication(array $data): StudentApplication
    {
        return $this->model->create($data);
    }

    public function updateApplication(StudentApplication $application, array $data): bool
    {
        return $application->update($data);
    }

    public function deleteApplication(StudentApplication $application): bool
    {
        return $application->delete();
    }

    public function approveApplication(StudentApplication $application, int $reviewerId): bool
    {
        return DB::transaction(function() use ($application, $reviewerId) {
            $application->approve($reviewerId);
            
            $student = Student::create([
                'school_id' => $application->school_id,
                'campus_id' => $application->campus_id,
                'student_number' => $this->generateStudentNumber($application->school_id),
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'dob' => $application->dob,
                'gender' => $application->gender,
                'email' => $application->email,
                'phone' => $application->phone,
                'parent_name' => $application->parent_name,
                'parent_email' => $application->parent_email,
                'parent_phone' => $application->parent_phone,
                'enrollment_date' => now()->toDateString(),
                'status' => Student::STATUS_ACTIVE,
            ]);

            return true;
        });
    }

    public function rejectApplication(StudentApplication $application, int $reviewerId): bool
    {
        return $application->reject($reviewerId);
    }

    public function getApplicationsByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['school', 'campus', 'reviewer'])
            ->get();
    }

    public function getApplicationsBySchool(int $schoolId): Collection
    {
        return $this->model->where('school_id', $schoolId)
            ->with(['campus', 'reviewer'])
            ->get();
    }

    public function getApplicationsByCampus(int $campusId): Collection
    {
        return $this->model->where('campus_id', $campusId)
            ->with(['school', 'reviewer'])
            ->get();
    }

    public function getApplicationsByReviewer(int $reviewerId): Collection
    {
        return $this->model->where('reviewer_id', $reviewerId)
            ->with(['school', 'campus'])
            ->get();
    }

    public function searchApplications(string $searchTerm): Collection
    {
        return $this->model->where(function($query) use ($searchTerm) {
            $query->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('parent_name', 'like', "%{$searchTerm}%");
        })
        ->with(['school', 'campus', 'reviewer'])
        ->get();
    }

    public function getApplicationStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        $total = $query->count();
        $pending = $query->where('status', StudentApplication::STATUS_PENDING)->count();
        $approved = $query->where('status', StudentApplication::STATUS_APPROVED)->count();
        $rejected = $query->where('status', StudentApplication::STATUS_REJECTED)->count();

        $recent = $query->where('applied_on', '>=', now()->subDays(30))->count();

        $byGender = $query->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->get();

        return [
            'total_applications' => $total,
            'pending_applications' => $pending,
            'approved_applications' => $approved,
            'rejected_applications' => $rejected,
            'recent_applications' => $recent,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'applications_by_gender' => $byGender,
        ];
    }

    public function isEmailRegistered(string $email): bool
    {
        $existingApplication = $this->model->where('email', $email)->exists();
        $existingStudent = Student::where('email', $email)->exists();
        
        return $existingApplication || $existingStudent;
    }

    public function getApplicationsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('applied_on', [$startDate, $endDate])
            ->with(['school', 'campus', 'reviewer'])
            ->get();
    }

    private function generateStudentNumber(int $schoolId): string
    {
        return Student::generateStudentNumber($schoolId);
    }
} 