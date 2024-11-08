<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Customer;

class CustomerRepository extends BaseRepository
{

    public function __construct(
        Customer $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('cities','users')->get();
    }

    public function getFileName($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->mou_file;
    }

    public function getCustomerName($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->customer_name;
    }

    public function getCustomerTax($id)
    {
        $data = $this->model->where('id', $id)->get()->first();
        return $data->tax;
    }

    public function show($id)
    {
        $data = $this->model->with('brands','branchs','pics')
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
