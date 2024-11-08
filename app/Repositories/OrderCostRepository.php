<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use App\Models\OrderCost;

class OrderCostRepository extends BaseRepository
{

    public function __construct(
        OrderCost $model
    ) {
        $this->model = $model;
    }

    public function getIndex($request)
    {
        return $this->model->with('users')->get();
    }

    public function show($id)
    {
        $data = $this->model
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function getSumTax(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('tax');
        return $data;
    }

    public function getSumNett(array $order_number)
    {
        /*$data = $this->model->whereIn('order_number', $order_number)->sum('nett');
        return $data;*/
        //$ords = implode(',', $order_number);
        /*$tmp = DB::select("select sum(z.total) as subtotal from
        (select (A.total_kg * B.price) as total
        from orders A
        join order_costs B on A.order_number=B.order_number
        where A.order_number in(".$ords.")
        ) as z");*/

        $price = DB::table('orders')
            ->join('order_costs', 'orders.order_number', '=', 'order_costs.order_number')
            ->whereIn('orders.order_number', $order_number)
            ->select(DB::raw('sum(orders.total_kg * order_costs.price) as subtotal'))
            ->first();
        
        $other = DB::table('orders')
            ->join('order_costs', 'orders.order_number', '=', 'order_costs.order_number')
            ->whereIn('orders.order_number', $order_number)
            ->select(DB::raw('sum(order_costs.packing_cost + order_costs.insurance_fee) as subtotal'))
            ->first();
        
        return $price->subtotal + $other->subtotal;
    }

    public function getSumPrice(array $order_number)
    {
        $tmp = DB::table('orders')
            ->join('order_costs', 'orders.order_number', '=', 'order_costs.order_number')
            ->whereIn('orders.order_number', $order_number)
            ->select(DB::raw('sum(order_costs.price + order_costs.packing_cost + order_costs.insurance_fee) as subtotal'))
            ->first();
        return $tmp->subtotal;
    }

    public function getSumGrandTotal(array $order_number)
    {
        $data = $this->model->whereIn('order_number', $order_number)->sum('grand_total');
        return $data;
    }

    public function getDistinctTaxPercent(array $order_number)
    {
        $data = $this->model->select('tax_percent')->whereIn('order_number', $order_number)->distinct()->get();
        return $data[0]->tax_percent;
    }

    public function isSameTaxPercent(array $order_number)
    {
        $count = $this->model->whereIn('order_number', $order_number)->distinct()->count('tax_percent');
        if($count > 1){
            return false;
        }else{
            return true;
        }
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
