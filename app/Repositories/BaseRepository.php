<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find record by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update existing record
     */
    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    /**
     * Delete record
     */
    public function delete($id): bool
    {
        $record = $this->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Get paginated results
     */
    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find records by criteria
     */
    public function findBy(array $criteria): Collection
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->get();
    }

    /**
     * Find first record by criteria
     */
    public function findFirstBy(array $criteria)
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->first();
    }

    /**
     * Get the model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the model instance
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }
} 