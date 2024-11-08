<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\CustomerMou;

class CustomerMouRepository extends BaseRepository
{

    public function __construct(
        CustomerMou $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getIndexByCustomerID($id)
    {
        $data = $this->model
            ->with('users')
            ->where('customer_id', $id)
            ->get();
        return $data;
    }

    public function getListByCustID($id)
    {
        $data = $this->model->where('customer_id', $id)->get();
        return $data;
    }
    
    public function getActiveCountDataByCustID($id)
    {
        //get data by end date active
        $data = $this->model->where('customer_id', $id)
            ->where('mou_end_date', '>=', date('Y-m-d'))
            ->count('id');
        return $data;
    }

    public function getMouFileName($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->mou_file;
    }

    public function getMouNumber($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->mou_number;
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

    public function deleteByCustID($id)
    {
        return $this->model->where('customer_id', $id)->delete();
    }
}
