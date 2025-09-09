<?php

namespace App\Repositories\AdminAcademics;

use App\Models\AdminAcademics\SubjectPrerequisite;
use App\Models\AdminAcademics\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PrerequisiteRepository implements PrerequisiteRepositoryInterface
{
    /**
     * Get all prerequisites with filters
     * @param array $filters The filters for the prerequisites
     * @return Collection The prerequisites
     */
    public function getAllPrerequisites(array $filters = []): Collection
    {
        $query = SubjectPrerequisite::with(['subject', 'prerequisite']);

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['prerequisite_id'])) {
            $query->where('prerequisite_id', $filters['prerequisite_id']);
        }

        $prerequisites = $query->orderBy('subject_id', 'asc')->get();

        return $prerequisites;
    }

    /**
     * Get a prerequisite by its ID
     * @param int $id The ID of the prerequisite
     * @return SubjectPrerequisite|null The prerequisite if found, null otherwise
     */
    public function getPrerequisiteById(int $id): ?SubjectPrerequisite
    {
        return SubjectPrerequisite::with(['subject', 'prerequisite'])->find($id);
    }

    /**
     * Create a new prerequisite
     * @param array $data The data for the prerequisite
     * @return SubjectPrerequisite The created prerequisite
     */
    public function createPrerequisite(array $data): SubjectPrerequisite
    {
        $prerequisite = SubjectPrerequisite::create($data);
        return $prerequisite->load(['subject', 'prerequisite']);
    }

    /**
     * Update an existing prerequisite
     * @param SubjectPrerequisite $prerequisite The prerequisite to update
     * @param array $data The data to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updatePrerequisite(SubjectPrerequisite $prerequisite, array $data): bool
    {
        return $prerequisite->update($data);
    }

    /**
     * Delete a prerequisite
     * @param SubjectPrerequisite $prerequisite The prerequisite to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deletePrerequisite(SubjectPrerequisite $prerequisite): bool
    {
        return $prerequisite->delete();
    }

    /**
     * Get all prerequisites for a specific subject
     * @param int $subjectId The ID of the subject
     * @return Collection The prerequisites for the subject
     */
    public function getPrerequisitesBySubject(int $subjectId): Collection
    {
        return SubjectPrerequisite::with(['prerequisite'])
            ->where('subject_id', $subjectId)
            ->orderBy('prerequisite_id', 'asc')
            ->get();
    }

    /**
     * Get all subjects that require a specific subject as prerequisite
     * @param int $prerequisiteId The ID of the prerequisite subject
     * @return Collection The subjects that require this prerequisite
     */
    public function getSubjectsRequiringPrerequisite(int $prerequisiteId): Collection
    {
        return SubjectPrerequisite::with(['subject'])
            ->where('prerequisite_id', $prerequisiteId)
            ->orderBy('subject_id', 'asc')
            ->get();
    }

    /**
     * Get prerequisite by subject and prerequisite IDs
     * @param int $subjectId The subject ID
     * @param int $prerequisiteId The prerequisite ID
     * @param int|null $excludeId The ID to exclude from search
     * @return SubjectPrerequisite|null The prerequisite if found
     */
    public function getPrerequisiteBySubjectAndPrerequisite(int $subjectId, int $prerequisiteId, ?int $excludeId = null): ?SubjectPrerequisite
    {
        $query = SubjectPrerequisite::where('subject_id', $subjectId)
            ->where('prerequisite_id', $prerequisiteId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    /**
     * Check for circular dependency
     * @param int $subjectId The subject ID
     * @param int $prerequisiteId The prerequisite ID
     * @return bool True if circular dependency exists
     */
    public function checkCircularDependency(int $subjectId, int $prerequisiteId): bool
    {
        // If we're trying to make A require B as prerequisite,
        // check if B already requires A (directly or indirectly)
        return $this->hasIndirectPrerequisite($prerequisiteId, $subjectId);
    }

    /**
     * Check if subject A has subject B as an indirect prerequisite
     * @param int $subjectId The subject ID
     * @param int $targetPrerequisiteId The target prerequisite ID
     * @return bool True if indirect prerequisite exists
     */
    private function hasIndirectPrerequisite(int $subjectId, int $targetPrerequisiteId): bool
    {
        // Get all direct prerequisites of the subject
        $directPrerequisites = SubjectPrerequisite::where('subject_id', $subjectId)
            ->pluck('prerequisite_id')
            ->toArray();

        // If target is a direct prerequisite, we found a circular dependency
        if (in_array($targetPrerequisiteId, $directPrerequisites)) {
            return true;
        }

        // Check each direct prerequisite recursively
        foreach ($directPrerequisites as $prerequisiteId) {
            if ($this->hasIndirectPrerequisite($prerequisiteId, $targetPrerequisiteId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the complete prerequisite chain for a subject
     * @param int $subjectId The subject ID
     * @return array The prerequisite chain
     */
    public function getPrerequisiteChain(int $subjectId): array
    {
        $chain = [];
        $visited = [];

        $this->buildPrerequisiteChain($subjectId, $chain, $visited);

        return $chain;
    }

    /**
     * Recursively build prerequisite chain
     * @param int $subjectId The subject ID
     * @param array $chain The chain being built
     * @param array $visited Visited subjects to prevent infinite loops
     */
    private function buildPrerequisiteChain(int $subjectId, array &$chain, array &$visited)
    {
        if (in_array($subjectId, $visited)) {
            return; // Prevent infinite loops
        }

        $visited[] = $subjectId;

        $prerequisites = SubjectPrerequisite::with(['prerequisite'])
            ->where('subject_id', $subjectId)
            ->get();

        foreach ($prerequisites as $prerequisite) {
            $prerequisiteSubject = $prerequisite->prerequisite;
            
            if (!isset($chain[$subjectId])) {
                $chain[$subjectId] = [
                    'subject' => $prerequisiteSubject,
                    'prerequisites' => []
                ];
            }

            $chain[$subjectId]['prerequisites'][] = [
                'id' => $prerequisite->id,
                'subject' => $prerequisiteSubject,
                'level' => 1
            ];

            // Recursively get prerequisites of this prerequisite
            $this->buildPrerequisiteChain($prerequisiteSubject->id, $chain, $visited);
        }
    }

    /**
     * Bulk import prerequisites
     * @param array $prerequisites The prerequisites to import
     * @return array The results of the import
     */
    public function bulkImportPrerequisites(array $prerequisites): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($prerequisites)
        ];

        DB::beginTransaction();

        try {
            foreach ($prerequisites as $index => $prerequisiteData) {
                try {
                    // Validate required fields
                    if (!isset($prerequisiteData['subject_id']) || !isset($prerequisiteData['prerequisite_id'])) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $prerequisiteData,
                            'error' => 'Missing required fields: subject_id and prerequisite_id'
                        ];
                        continue;
                    }

                    // Check if subject and prerequisite are different
                    if ($prerequisiteData['subject_id'] == $prerequisiteData['prerequisite_id']) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $prerequisiteData,
                            'error' => 'Subject cannot be a prerequisite of itself'
                        ];
                        continue;
                    }

                    // Check if prerequisite already exists
                    $existing = $this->getPrerequisiteBySubjectAndPrerequisite(
                        $prerequisiteData['subject_id'],
                        $prerequisiteData['prerequisite_id']
                    );

                    if ($existing) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $prerequisiteData,
                            'error' => 'This prerequisite relationship already exists'
                        ];
                        continue;
                    }

                    // Check for circular dependency
                    if ($this->checkCircularDependency(
                        $prerequisiteData['subject_id'],
                        $prerequisiteData['prerequisite_id']
                    )) {
                        $results['failed'][] = [
                            'index' => $index,
                            'data' => $prerequisiteData,
                            'error' => 'Circular dependency detected'
                        ];
                        continue;
                    }

                    $prerequisite = SubjectPrerequisite::create($prerequisiteData);
                    $results['success'][] = [
                        'index' => $index,
                        'data' => $prerequisite->load(['subject', 'prerequisite']),
                        'message' => 'Prerequisite created successfully'
                    ];
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'index' => $index,
                        'data' => $prerequisiteData,
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