<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

//use App\Models\Order;

class DashboardRepository extends BaseRepository
{

    public function __construct() {
        //
    }

    public function getDataTotalOrder($request)
    {
        $data = DB::select("select count(id) as total from orders
        where month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        and branch_id=".auth()->user()->branch_id."");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getDataTotalNewCustomer($request)
    {
        $data = DB::select("select count(id) as total from customers
        where month(created_at) = month(curdate())
        and year(created_at) = year(curdate())");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getTotalOrderOnDelivery($request)
    {
        $data = DB::select("select count(id) as total from orders
        where last_status <> 'Delivered' and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        and branch_id=".auth()->user()->branch_id."");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getTotalOrderDelivered($request)
    {
        $data = DB::select("select count(id) as total from orders
        where last_status = 'Delivered' and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        and branch_id=".auth()->user()->branch_id."");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getDataOrderMonthly($request)
    {
        $data = DB::select("select date_format(pickup_date,'%Y-%m-%d') as date, 
        count(id) as total from orders
        where month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        and branch_id=".auth()->user()->branch_id."
        group by date_format(pickup_date,'%Y-%m-%d')
        order by date_format(pickup_date,'%Y-%m-%d')");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getNewCustomer($request)
    {
        $data = DB::select("select id, customer_code, customer_name from customers
        where month(created_at) = month(curdate())
        and year(created_at) = year(curdate())
        order by customer_code desc");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getNewOrder($request)
    {
        $data = DB::select("select awb_no, total_kg, last_status, 
        date_format(pickup_date, '%d-%M-%y') as date from orders
        where month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        and branch_id=".auth()->user()->branch_id."
        order by awb_no desc limit 5");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    public function getOrderRealTime($request)
    {
        $data = DB::select("select ifnull(sum(A.total_kg),0) as kg, ifnull(sum(B.grand_total),0) as total
        from orders A
        join order_costs B on A.order_number=B.order_number
        where year(A.pickup_date) = year(curdate())
        and month(A.pickup_date) = month(curdate())
        and day(A.pickup_date) = day(curdate())
        and branch_id=".auth()->user()->branch_id."");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    /*public function show($id)
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
    }*/
}
