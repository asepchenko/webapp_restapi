<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\City;

class CityRepository extends BaseRepository
{

    public function __construct(
        City $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('provinces','users')->get();
    }

    public function getByCityName($name)
    {
        $data = DB::select("select id, city_name from cities where city_name like '%".$name."%' 
        order by city_name desc");

        if(count($data) > 0){
            return $data;
        }else{
            return NULL;
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
}
