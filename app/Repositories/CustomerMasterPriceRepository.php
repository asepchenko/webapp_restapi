<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Location;
use App\Models\LocationRate;
use App\Models\CustomerMasterPrice;

class CustomerMasterPriceRepository extends BaseRepository
{

    public function __construct(
        CustomerMasterPrice $model,
        Location $locationModel,
        LocationRate $locationRateModel
    ) {
        $this->model = $model;
        $this->locationModel = $locationModel;
        $this->locationRateModel = $locationRateModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('locations','services','users')->get();
    }

    public function getIndexPending($request)
    {
        return $this->model->with('locations','services','users','customers')
        ->where('status','PENDING')->get();
    }

    public function getIndexByCustomerID($id)
    {
        $data = $this->model
            ->with('locations','services','users')
            ->where('customer_id', $id)
            ->get();
        return $data;
    }
    
    public function getMasterPriceRates($customer, $origin, $destination, $service)
    {
        $location = $this->locationModel->where('origin', $origin)
                                        ->where('destination',$destination)
                                        ->where('service_id', $service)
                                        ->get('id');
        if(count($location) > 0){
            $data = $this->model
                ->where('customer_id', $customer)
                ->where('location_id', $location[0]->id)
                ->where('service_id', $service)
                ->get();
            if(count($data) > 0){
                return $data[0];
            }else{
                return NULL;
            }
        }else{
            return NULL;
        }
    }

    public function show($id)
    {
        $data = $this->model->with('locations','services','users')
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
    public function checkAlreadyExist($cust_id, $price_code)
    {
        $check = $this->model
            ->where('customer_id', $cust_id)
            ->where('price_code', $price_code)->count();
        if($check > 0){
            return true;
        }else{
            return false;
        }
    }

    public function checkIsHasUsed($id)
    {
        $check = DB::select("select B.order_number
        from customer_master_prices A
        join orders B on A.id=B.customer_master_price_id
        where A.id=".$id);
        if(count($check) > 0){
            return true;
        }else{
            return false;
        }
    }

    public function deleteByCustID($id)
    {
        return $this->model->where('customer_id', $id)->delete();
    }
}
