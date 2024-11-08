<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

//use App\Models\CustomerPic;
use App\Models\UserCustomer;

class CustomerPicRepository extends BaseRepository
{

    public function __construct(
        UserCustomer $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->get();
    }

    public function getIndexByCustomerID($id)
    {
        $data = $this->model
            ->where('customer_id', $id)
            ->get();
        return $data;
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function getActiveData($cust_id)
    {
        return $this->model->where('customer_id', $cust_id)
                            ->where('approved',1)->get();
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
