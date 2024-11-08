<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\TripCity;

class TripCityRepository extends BaseRepository
{

    public function __construct(
        TripCity $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getByTripNumber($no)
    {
        $data = $this->model->with('cities')
            ->where('trip_number', $no)
            ->get();
        return $data;
    }
    
    public function getSumKilogram($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->sum('kg');
        return $data;
    }

    public function getSumCogsKg($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)
        ->sum('cogs_kg');
        return $data;
    }

    public function getSumCogsKgByTrip($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->sum('cogs_kg');
        return $data;
    }

    public function getCountDataByTrip($trip_number)
    {
        $data = $this->model->where('trip_number', $trip_number)->count('city_id');
        return $data;
    }

    public function getDataByTripNumberAndCityID($trip_number, $city_id)
    {
        $data = $this->model->where('trip_number', $trip_number)
            ->where('city_id', $city_id)
            ->get(['cogs_kg','kg','multiplier_number','id']);
        return $data;//[0]; //->cogs_kg;
    }

    public function getOrderNumberByTripNumber($trip_number)
    {
        /*$data = DB::select("select E.order_number
        from trip_details B
        join manifest_details D on B.manifest_number = D.manifest_number
        join orders E on D.order_number = E.order_number
        where B.trip_number='".$trip_number."'");*/

        $data = DB::table('trip_details')
            ->join('manifest_details', 'trip_details.manifest_number', '=', 'manifest_details.manifest_number')
            ->join('orders', 'manifest_details.order_number', '=', 'orders.order_number')
            ->where('trip_details.trip_number', $trip_number)
            ->selectRaw('orders.order_number as order_number')
            ->get(['order_number'])->toArray();
        return $data;
    }

    public function getDataByID($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get(['cogs_kg','kg','multiplier_number']);
        return $data;
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

    public function updateByTripNumberAndCityID(array $data, $trip_number, $city_id)
    {
        $object = $this->model->where('trip_number', $trip_number)
            ->where('city_id', $city_id)
            ->firstOrFail();
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
}
