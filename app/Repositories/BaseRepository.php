<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Http\Request;

//use App\Filters\BaseFilter;
//use App\Helpers\Pagination;

//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;
    //protected $env;
    //protected $productionEnv = 'production';

    public function getAll(Request $request)
    {
        return ($this->model)->newQuery();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getByMultipleField(array $conditions)
    {
        $data = $this->model::where(function ($q) use ($conditions) {
            foreach ($conditions as $column => $value) {
                $q->where($column, $value);
            }
        })->get();

        return $data;
    }

    public function create(array $data)
    {
        return $this->model::create($data);
    }

    
    public function update(array $data, $id)
    {
        $object = $this->model->findOrFail($id);
        return $object->update($data);
    }

    public function delete($id)
    {
        $object = $this->model::findOrFail($id);
        $object->delete();
        return 204;
    }

    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    public function take($take)
    {
        $this->model = $this->model->take($take);
        return $this;
    }

    public function skip($skip)
    {
        $this->model = $this->model->skip($skip);
        return $this;
    }

    public function createMany($relation, array $attributes)
    {
        return $relation->createMany($attributes);
    }

    public function updateOrCreate(array $dataToCheck, array $dataToInput)
    {
        return $this->model->updateOrCreate(
            $dataToCheck,
            $dataToInput
        );
    }
}
