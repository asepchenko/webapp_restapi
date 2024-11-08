<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\AreaCity;

class AreaCityRepository extends BaseRepository
{

    public function __construct(
        AreaCity $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','cities')->get();
    }

    public function getIndexByAreaID($id)
    {
        return $this->model->where('area_id',$id)->with('users','cities')->get();
    }

    public function isAlreadyExist($area_id, $city_id)
    {
        $data = $this->model->where('area_id', $area_id)
                            ->where('city_id', $city_id)
                            ->get('id');
        if(count($data)>0){
            return true;
        }else{
            return false;
        }
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

    public function deleteByAreaID($id)
    {
        return $this->model->where('area_id', $id)->delete();
    }
}
