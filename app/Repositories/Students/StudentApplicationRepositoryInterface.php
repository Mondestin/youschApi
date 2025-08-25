<?php

namespace App\Repositories\Students;

use App\Models\Students\StudentApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentApplicationRepositoryInterface
{
    /**
     * Get paginated applications with filters
     */
    public function getPaginatedApplications(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get application by ID with relationships
     */
    public function getApplicationById(int $id, array $relationships = []): ?StudentApplication;

    /**
     * Create a new application
     */
    public function createApplication(array $data): StudentApplication;

    /**
     * Update application
     */
    public function updateApplication(StudentApplication $application, array $data): bool;

    /**
     * Delete application
     */
    public function deleteApplication(StudentApplication $application): bool;

    /**
     * Approve application
     */
    public function approveApplication(StudentApplication $application, int $reviewerId): bool;

    /**
     * Reject application
     */
    public function rejectApplication(StudentApplication $application, int $reviewerId): bool;

    /**
     * Get applications by status
     */
    public function getApplicationsByStatus(string $status): Collection;

    /**
     * Get applications by school
     */
    public function getApplicationsBySchool(int $schoolId): Collection;

    /**
     * Get applications by campus
     */
    public function getApplicationsByCampus(int $campusId): Collection;

    /**
     * Get applications by reviewer
     */
    public function getApplicationsByReviewer(int $reviewerId): Collection;

    /**
     * Search applications
     */
    public function searchApplications(string $searchTerm): Collection;

    /**
     * Get application statistics
     */
    public function getApplicationStatistics(array $filters = []): array;

    /**
     * Check if email is already registered
     */
    public function isEmailRegistered(string $email): bool;

    /**
     * Get applications by date range
     */
    public function getApplicationsByDateRange(string $startDate, string $endDate): Collection;
} 