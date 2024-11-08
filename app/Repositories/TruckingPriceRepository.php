<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\TruckingPrice;

class TruckingPriceRepository extends BaseRepository
{

    public function __construct(
        TruckingPrice $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users','trucktypes','origins','destinations')->get();
    }

    public function getTruckingPriceRates($origin, $destination, $type)
    {
        $data = $this->model->with('origins','destinations')
                            ->where('origin', $origin)
                            ->where('destination',$destination)
                            ->where('truck_type_id',$type)
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
            ->with('trucktypes','origins','destinations')
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
