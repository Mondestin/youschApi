<?php

namespace App\Repositories\AdminAcademics;

use App\Models\AdminAcademics\SubjectPrerequisite;
use Illuminate\Database\Eloquent\Collection;

interface PrerequisiteRepositoryInterface
{
    public function getAllPrerequisites(array $filters = []): Collection;
    public function getPrerequisiteById(int $id): ?SubjectPrerequisite;
    public function createPrerequisite(array $data): SubjectPrerequisite;
    public function updatePrerequisite(SubjectPrerequisite $prerequisite, array $data): bool;
    public function deletePrerequisite(SubjectPrerequisite $prerequisite): bool;
    public function getPrerequisitesBySubject(int $subjectId): Collection;
    public function getSubjectsRequiringPrerequisite(int $prerequisiteId): Collection;
    public function getPrerequisiteBySubjectAndPrerequisite(int $subjectId, int $prerequisiteId, ?int $excludeId = null): ?SubjectPrerequisite;
    public function checkCircularDependency(int $subjectId, int $prerequisiteId): bool;
    public function getPrerequisiteChain(int $subjectId): array;
    public function bulkImportPrerequisites(array $prerequisites): array;
}