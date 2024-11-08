<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\OrderCostAgent;

class OrderCostAgentRepository extends BaseRepository
{

    public function __construct(
        OrderCostAgent $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getCostByAgent($order_number, $agent_id)
    {
        $data = $this->model->where('order_number', $order_number)
                            ->where('agent_id', $agent_id)
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

    public function deleteByOrderNumberAgentID($order_number, $agent_id)
    {
        return $this->model->where('order_number', $order_number)->where('agent_id', $agent_id)->delete();
    }
}
