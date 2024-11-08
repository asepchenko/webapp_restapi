<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\LimitTime;

class LimitTimeRepository extends BaseRepository
{

    public function __construct(
        LimitTime $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('locations','users')->get();
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createWithID(array $data)
    {
        $id = $this->model->create($data)->id;
        return $id;
    }

    public function update(array $data, $id)
    {
        $object = $this->model->findOrFail($id);
        $object->fill($data);
        $object->save();
        return $object->fresh();
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    //additional
    public function checkAlreadyExist($location_id)
    {
        $check = $this->model
            ->where('location_id', $location_id)->count();
        if($check > 0){
            return true;
        }else{
            return false;
        }
    }
}
