<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Agent;
use App\Models\Order;
use App\Models\Location;
use App\Models\LocationRate;
use App\Models\AgentMasterPrice;

class AgentMasterPriceRepository extends BaseRepository
{

    public function __construct(
        Agent $agentModel,
        AgentMasterPrice $model,
        Order $orderModel,
        Location $locationModel,
        LocationRate $locationRateModel
    ) {
        $this->model = $model;
        $this->agentModel = $agentModel;
        $this->orderModel = $orderModel;
        $this->locationModel = $locationModel;
        $this->locationRateModel = $locationRateModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByAgentID($id)
    {
        $data = $this->model
            ->with('locations','services','users')
            ->where('agent_id', $id)
            ->get();
        return $data;
    }

    //get price agent from transit to destination
    /*public function getPriceByOrderNumber($order_number, $agent_id)
    {
        $order = $this->orderModel->where('order_number', $order_number)->first();
        $agent = $this->agentModel->where('id', $agent_id)->first();
        $location = $this->locationModel->where('origin', $agent->city_id)
                                        ->where('destination',$order->destination)
                                        ->get('id');
        if($location){
            $data = $this->model
                ->where('agent_id', $agent_id)
                ->where('location_id', $location[0]->id)
                ->where('service_id', $order->service_id)
                ->first();
            if($data){
                return $data->price;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }*/

    public function getPriceByOrderNumber($order_number, $agent_id, $origin, $destination)
    {
        $order = $this->orderModel->where('order_number', $order_number)->first();
        $agent = $this->agentModel->where('id', $agent_id)->first();
        $location = $this->locationModel->where('origin', $origin)
                                        ->where('destination',$destination)
                                        ->get('id');
        if(count($location)>0){
            $data = $this->model
                ->where('agent_id', $agent_id)
                ->where('location_id', $location[0]->id)
                ->where('service_id', $order->service_id)
                ->first();
            if($data){
                return $data->price;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    public function getMasterPriceRates($agent, $origin, $destination, $service)
    {
        $location = $this->locationModel->where('origin', $origin)
                                        ->where('destination',$destination)
                                        ->get('id');
        if(count($location)){
            $data = $this->model
                ->where('agent_id', $agent)
                ->where('location_id', $location[0]->id)
                ->where('service_id', $service)
                ->firstOrFail();
            if($data){
                return $data;
            }else{
                return NULL;
            }
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

    //additional
    public function checkAlreadyExist($agent_id, $price_code)
    {
        $check = $this->model
            ->where('agent_id', $agent_id)
            ->where('price_code', $price_code)->count();
        if($check > 0){
            return true;
        }else{
            return false;
        }
    }

    public function deleteByAgentID($id)
    {
        return $this->model->where('agent_id', $id)->delete();
    }
}
