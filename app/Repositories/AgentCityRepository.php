<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\AgentCity;

class AgentCityRepository extends BaseRepository
{

    public function __construct(
        AgentCity $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByAgentID($id)
    {
        $data = $this->model
            ->with('cities','users')
            ->where('agent_id', $id)
            ->get();
        return $data;
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    //additional
    public function checkAlreadyExist($agent_id, $city_id)
    {
        $check = $this->model->withTrashed()
            ->where('agent_id', $agent_id)
            ->where('city_id', $city_id)->count();
        if($check > 0){
            return true;
        }else{
            return false;
        }
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
