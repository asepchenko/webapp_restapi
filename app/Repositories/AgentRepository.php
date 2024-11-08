<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Agent;
use App\Models\AgentCity;
use App\Models\AreaCity;

class AgentRepository extends BaseRepository
{

    public function __construct(
        Agent $model,
        AgentCity $modelCity,
        AreaCity $areaCityModel
    ) {
        $this->model = $model;
        $this->modelCity = $modelCity;
        $this->areaCityModel = $areaCityModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('areas','cities','users')->get();
    }

    public function getIndexByCityID($city_id)
    {
        //get area id by city
        /*$tmp = $this->areaCityModel->where('city_id', $city_id)->get('area_id');
        if(count($tmp)>0 ){
            //$area_id = $tmp[0]->area_id;
            $data = $this->model
                ->with('areas')
                ->whereIn('area_id', $tmp)
                ->get();
            return $data;
        }else{
            return NULL;
        }*/

        $data = DB::select("select B.id, B.agent_name
        from agent_cities A
        join agents B on A.agent_id=B.id
        where A.city_id =".$city_id." and A.deleted_at is NULL");
        if(count($data)>0 ){
            return $data;
        }else{
            return NULL;
        }
    }

    public function getIndexByCityAddressID($id)
    {
        $data = $this->model
                ->where('city_id', $id)
                ->get();
                
        if(count($data)){
            return $data;
        }else{
            return NULL;
        }
    }

    public function getActiveCountData($id)
    {
        //get data by end date active
        $data = $this->model->where('id', $id)
            ->where('mou_end_date', '>=', date('Y-m-d'))
            ->count('id');
        return $data;
    }

    public function getAgentByAreaID($area_id)
    {
        $data = $this->model->where('area_id', $area_id)->get('id');
        return $data;
    }

    public function getFileName($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->mou_file;
    }
    
    public function getAgentCityID($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->city_id;
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