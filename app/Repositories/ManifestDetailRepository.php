<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\ManifestDetail;

class ManifestDetailRepository extends BaseRepository
{

    public function __construct(
        ManifestDetail $model,
        Order $orderModel
    ) {
        $this->model = $model;
        $this->orderModel = $orderModel;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function getListOrderByManifestNumber($manifest_number)
    {
        $order = $this->model
            ->where('manifest_number', $manifest_number)
            ->get('order_number')->toArray();
        
        //return $data;
        if(count($order) > 0){
                $data = $this->orderModel
                    ->whereIn('order_number', $order)
                    ->where('last_status', '<>', 'Delivered')
                    ->get('order_number');
                if(count($data) > 0){
                    return $data;
                }else{
                    return NULL;
                }
            }else{
                return NULL;
            }
    }

    public function getArrayListOrderByManifestNumber(array $manifest_number)
    {
        $data = $this->model
            ->whereIn('manifest_number', $manifest_number)
            ->get('order_number')->toArray();
        return $data;
    }

    public function getArrayListOrderByOneManifestNumber($manifest_number)
    {
        $data = $this->model
            ->where('manifest_number', $manifest_number)
            ->get('order_number')->toArray();
        return $data;
    }

    public function getOrderNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get();
        return $data[0]->order_number;
    }

    public function getManifestNumber($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->get();
        return $data[0]->manifest_number;
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

    public function deleteByManifestNumber($manifest_number)
    {
        return $this->model->where('manifest_number', $manifest_number)->delete();
    }
}
