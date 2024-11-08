<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\CustomerTruckingPrice;

class CustomerTruckingPriceRepository extends BaseRepository
{

    public function __construct(
        CustomerTruckingPrice $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('origins','destinations','trucktypes','users')->get();
    }

    public function getIndexPending($request)
    {
        return $this->model->with('origins','destinations','trucktypes','users','customers')
        ->where('status','PENDING')->get();
    }

    public function getIndexByCustomerID($id)
    {
        $data = $this->model
            ->with('origins','destinations','trucktypes','users')
            ->where('customer_id', $id)
            ->get();
        return $data;
    }

    public function getTruckingPriceRates($customer, $origin, $destination, $truck)
    {
        $data = $this->model
                ->where('customer_id', $customer)
                ->where('origin', $origin)
                ->where('destination', $destination)
                ->where('truck_Type_id', $truck)
                ->get();
        if(count($data) > 0){
            return $data[0];
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
    public function checkAlreadyExist($cust_id, $price_code, $truck_type)
    {
        $check = $this->model
            ->where('customer_id', $cust_id)
			->where('truck_type_id', $truck_type)
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
        from customer_trucking_prices A
        join orders B on A.id=B.trucking_price_id
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
