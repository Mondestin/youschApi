<?php

namespace App\Repositories\AdminAcademics;

use App\Models\AdminAcademics\Lab;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LabRepository implements LabRepositoryInterface
{
    /**
     * Get all labs with filters
     * @param array $filters The filters for the labs
     * @return Collection The labs
     */
    public function getAllLabs(array $filters = []): Collection
    {
        $query = Lab::with(['subject.course', 'subject.coordinator', 'assistant']);

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['description'])) {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
        }

        if (isset($filters['schedule'])) {
            $query->where('schedule', 'like', '%' . $filters['schedule'] . '%');
        }

        if (isset($filters['assistant_id'])) {
            $query->where('assistant_id', $filters['assistant_id']);
        }

        if (isset($filters['start_datetime_from'])) {
            $query->where('start_datetime', '>=', $filters['start_datetime_from']);
        }

        if (isset($filters['start_datetime_to'])) {
            $query->where('start_datetime', '<=', $filters['start_datetime_to']);
        }

        if (isset($filters['end_datetime_from'])) {
            $query->where('end_datetime', '>=', $filters['end_datetime_from']);
        }

        if (isset($filters['end_datetime_to'])) {
            $query->where('end_datetime', '<=', $filters['end_datetime_to']);
        }

        $labs = $query->orderBy('name', 'asc')->get();

        return $labs;
    }

    /**
     * Get a lab by its ID
     * @param int $id The ID of the lab
     * @return Lab|null The lab if found, null otherwise
     */
    public function getLabById(int $id): ?Lab
    {
        return Lab::with(['subject.course', 'subject.coordinator', 'assistant'])->find($id);
    }

    /**
     * Create a new lab
     * @param array $data The data for the lab
     * @return Lab The created lab
     */
    public function createLab(array $data): Lab
    {
        $lab = Lab::create($data);
        return $lab->load(['subject.course', 'subject.coordinator', 'assistant']);
    }

    /**
     * Update an existing lab
     * @param Lab $lab The lab to update
     * @param array $data The data to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateLab(Lab $lab, array $data): bool
    {
        return $lab->update($data);
    }

    /**
     * Delete a lab
     * @param Lab $lab The lab to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteLab(Lab $lab): bool
    {
        return $lab->delete();
    }

    /**
     * Get all labs for a specific subject
     * @param int $subjectId The ID of the subject
     * @return Collection The labs for the subject
     */
    public function getLabsBySubject(int $subjectId): Collection
    {
        return Lab::with(['subject.course', 'subject.coordinator', 'assistant'])
            ->where('subject_id', $subjectId)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Bulk import labs
     * @param array $labs The labs to import
     * @return array The results of the import
     */
    public function bulkImportLabs(array $labs): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($labs)
        ];

        DB::beginTransaction();

        try {
            foreach ($labs as $index => $labData) {
                try {
                    // Validate required fields
                    if (!isset($labData['subject_id']) || !isset($labData['name'])) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $labData,
                            'error' => 'Missing required fields: subject_id and name'
                        ];
                        continue;
                    }

                    // Check if lab already exists for this subject
                    $existing = Lab::where('subject_id', $labData['subject_id'])
                        ->where('name', $labData['name'])
                        ->first();

                    if ($existing) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $labData,
                            'error' => 'Lab with this name already exists for the subject'
                        ];
                        continue;
                    }

                    $lab = Lab::create($labData);
                    $results['success'][] = [
                        'index' => $index,
                        'data' => $lab->load(['subject']),
                        'message' => 'Lab created successfully'
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'index' => $index,
                        'data' => $labData,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results['error'] = 'Bulk import failed: ' . $e->getMessage();
        }

        return $results;
    }
}