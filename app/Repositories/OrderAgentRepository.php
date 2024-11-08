<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\OrderAgent;

class OrderAgentRepository extends BaseRepository
{

    public function __construct(
        OrderAgent $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getSequence($order_number)
    {
        $data = DB::select("select count(id)+1 as seq from order_agents
        where order_number = '".$order_number."'");
        return $data[0]->seq;
    }

    public function getIndexByOrderID($id)
    {
        /*$data = $this->model
            ->with('agents')
            ->where('order_number', $id)
            ->get();
        */
        $data = DB::select("select z.* from(
        select A.id as id, B.agent_code as agent_code, B.agent_name as agent_name, C.city_name as origin, 
        D.city_name as destination, A.sequence as sequence
        from order_agents A
        join agents B on A.agent_id = B.id
        left join cities C on A.origin = C.id
        left join cities D on A.destination = D.id
        where A.order_number = '".$id."' and A.branch_id is null
        union all
        select A.id as id, B.branch_code as agent_code, B.branch_name as agent_name, C.city_name as origin, 
        D.city_name as destination, A.sequence as sequence
        from order_agents A
        join branchs B on A.branch_id=B.id
        left join cities C on A.origin = C.id
        left join cities D on A.destination = D.id
        where A.order_number = '".$id."' and A.agent_id is null
        )as z
        order by z.sequence");
        if(count($data) >0){
            return $data;
        }else{
            return NULL;
        }
        
    }

    public function isSameAgent(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('agent_id');
        if($count > 1){
            return false;
        }else{
            return true;
        }
    }

    public function isSameSequence($order_number, $seq)
    {
        $data = $this->model->where('sequence', $seq)
                             ->where('order_number', $order_number)
                             ->get();
        if(count($data) >0){
            return true;
        }else{
            return false;
        }
    }

    public function getAgentID($id)
    {
        $data = $this->model->where('id', $id)->first();
        //$data = $this->model->where('id', $id)->where('order_number', $order_number)->first();
        if($data){
            return $data->agent_id;
        }else{
            return NULL;
        }
    }

    public function getBranchID($id)
    {
        $data = $this->model->where('branch_id', $id)->first();
        if($data){
            return $data->branch_id;
        }else{
            return NULL;
        }
    }

    public function getAgentOriginID($id)
    {
        $data = $this->model->where('id', $id)->first();
        return $data->origin_id;
    }

    public function getAgentByID($id)
    {
        $data = $this->model->where('id', $id)->first();
        return $data->agent_id;
    }

    public function getBranchByID($id)
    {
        $data = $this->model->where('id', $id)->first();
        return $data->branch_id;
    }

    public function getOrderNumber($id)
    {
        $data = $this->model->where('id', $id)->first();
        return $data->order_number;
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

    public function deleteByOrderNumber($order_number)
    {
        return $this->model->where('order_number', $order_number)->delete();
    }
    
    //additional
    public function checkAlreadyExist($order_number, $agent_id, $type)
    {
        if($type == "A"){
            $check = $this->model
                ->where('order_number', $order_number)
                ->where('agent_id', $agent_id)->count();
        }else{
            $check = $this->model
                ->where('order_number', $order_number)
                ->where('branch_id', $agent_id)->count();
        }

        if($check > 0){
            return true;
        }else{
            return false;
        }
    }
}