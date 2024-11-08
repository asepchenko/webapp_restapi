<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\OrderUnit;

class OrderUnitRepository extends BaseRepository
{

    public function __construct(
        OrderUnit $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByOrderID($id)
    {
        $data = $this->model
            ->where('order_number', $id)
            ->get();
        if(count($data) >0){
            return $data;
        }else{
            return NULL;
        }
        
    }
    
    public function getSumColly($order_number)
    {
        $data = $this->model->where('order_number', $order_number)->sum('colly');
        return $data;
    }

    public function getSumVolume($order_number)
    {
        $data = $this->model->where('order_number', $order_number)->sum('volume');
        return $data;
    }

    public function getSumCollyExceptID($order_number, $id)
    {
        $data = $this->model->where('order_number', $order_number)
                            ->where('id', '<>' , $id)
                            ->sum('colly');
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

    public function updateByOrderNumber(array $data, $order_number)
    {
        $object = $this->model->where('order_number',$order_number)->firstOrFail();
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
}
