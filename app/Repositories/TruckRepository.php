<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Truck;

class TruckRepository extends BaseRepository
{

    public function __construct(
        Truck $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('trucktypes','users')->get();
    }

    public function getIndexByTypeID($truck_type_id)
    {
        return $this->model->where('truck_type_id', $truck_type_id)
                            ->with('trucktypes','users')->get();
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
}
