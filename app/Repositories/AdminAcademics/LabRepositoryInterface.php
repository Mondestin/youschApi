<?php

namespace App\Repositories\AdminAcademics;

use App\Models\AdminAcademics\Lab;
use Illuminate\Database\Eloquent\Collection;

interface LabRepositoryInterface
{
    public function getAllLabs(array $filters = []): Collection;
    public function getLabById(int $id): ?Lab;
    public function createLab(array $data): Lab;
    public function updateLab(Lab $lab, array $data): bool;
    public function deleteLab(Lab $lab): bool;
    public function getLabsBySubject(int $subjectId): Collection;
    public function bulkImportLabs(array $labs): array;
}