<?php

namespace App\Http\Controllers\API\Customer;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerDashboardsController extends Controller
{
    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        $data_total_order = $this->getDataTotalOrder();
        $data_total_invoice = $this->getDataTotalInvoice();
        $data_total_order_on_delivery = $this->getTotalOrderOnDelivery();
        $data_total_order_delivered = $this->getTotalOrderDelivered();
        $data_order_monthly = $this->getDataOrderMonthly();
        $data_recent_order = $this->getDataRecentorder();
        
        $data = [
            'total_order' => $data_total_order,
            'total_invoice' => $data_total_invoice,
            'total_order_on_delivery' => $data_total_order_on_delivery,
            'total_order_delivered' => $data_total_order_delivered,
            'order_monthly' => $data_order_monthly,
            'recent_order' => $data_recent_order
        ];
                    
        return ResponseFormatter::success($data,'OK');
    }

    //get data
    private function getDataTotalOrder(){
        $data = DB::select("select count(id) as total from orders
        where customer_id=".auth()->user()->customer_id."
        and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataTotalInvoice(){
        $data = DB::select("select count(id) as total from invoices
        where customer_id=".auth()->user()->customer_id."
        and last_status in ('Sent','Payment')");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getTotalOrderOnDelivery(){
        $data = DB::select("select count(id) as total from orders
        where customer_id=".auth()->user()->customer_id."
        and last_status <> 'Delivered' and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getTotalOrderDelivered(){
        $data = DB::select("select count(id) as total from orders
        where customer_id=".auth()->user()->customer_id."
        and last_status = 'Delivered' and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataOrderMonthly(){
        $data = DB::select("select date_format(pickup_date,'%Y-%m-%d') as date, 
        count(id) as total from orders
        where customer_id=".auth()->user()->customer_id."
        and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        group by date_format(pickup_date,'%Y-%m-%d')
        order by date_format(pickup_date,'%Y-%m-%d')");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataRecentOrder(){
        $data = DB::select("select awb_no, total_kg, last_status, 
        date_format(pickup_date, '%d-%M-%y') as date from orders
        where customer_id=".auth()->user()->customer_id."
        and month(pickup_date) = month(curdate())
        and year(pickup_date) = year(curdate())
        order by awb_no desc limit 5");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }
}