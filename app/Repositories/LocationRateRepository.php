<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\LocationRate;

class LocationRateRepository extends BaseRepository
{

    public function __construct(
        LocationRate $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('locations','services','users')->get();
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
    public function checkAlreadyExist($location_id, $service_id)
    {
        $check = $this->model
            ->where('location_id', $location_id)
            ->where('service_id', $service_id)->count();
        if($check > 0){
            return true;
        }else{
            return false;
        }
    }
}
