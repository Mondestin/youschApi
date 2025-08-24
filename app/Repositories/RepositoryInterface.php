<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all();

    /**
     * Find record by ID
     */
    public function find($id);

    /**
     * Create new record
     */
    public function create(array $data);

    /**
     * Update existing record
     */
    public function update($id, array $data);

    /**
     * Delete record
     */
    public function delete($id);

    /**
     * Get paginated results
     */
    public function paginate($perPage = 15);

    /**
     * Find records by criteria
     */
    public function findBy(array $criteria);

    /**
     * Find first record by criteria
     */
    public function findFirstBy(array $criteria);
} 