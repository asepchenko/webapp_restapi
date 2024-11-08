<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Trip;

class TripRepository extends BaseRepository
{

    public function __construct(
        Trip $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','details')->get();
    }

    public function getDataTripByID($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get(['trip_number','operational_cost','multiplier_number']);
        return $data; //[0]; //->trip_number;
    }

    public function getLastStatusByTripNumber($trip_number)
    {
        $data = $this->model
            ->where('trip_number', $trip_number)
            ->get('last_status');
        return $data[0]->last_status;
    }

    public function getLastTrackingStatusByTripNumber($trip_number)
    {
        $data = $this->model
            ->where('trip_number', $trip_number)
            ->get('last_tracking_status');
        return $data[0]->last_tracking_status;
    }

    public function show($id)
    {
        $data = $this->model->with('details')
            ->where('trip_number', $id)
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

    public function deleteByTripNumber($trip_number)
    {
        return $this->model->where('trip_number', $trip_number)->delete();
    }

    public function updateOrdersByTripID($id, $user_id)
    {
        $orders = DB::select("select E.order_number
        from trips A
        join trip_details B on A.trip_number=B.trip_number
        join manifest_details D on B.manifest_number = D.manifest_number
        join orders E on D.order_number = E.order_number
        where A.id=".$id."");

        $data = array(
            'last_status_acc'   => 'Sales',
            'user_id'    	    => $user_id
        );

        foreach($orders as $order) {
            DB::table('orders')
                ->where('order_number', $order->order_number)
                ->update($data);
        }
        
        return true;
    }
}
