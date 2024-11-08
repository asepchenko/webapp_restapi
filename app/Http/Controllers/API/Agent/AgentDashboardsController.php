<?php

namespace App\Http\Controllers\API\Agent;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentDashboardsController extends Controller
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
        $data = DB::select("select count(A.id) as total from order_agents A
        join orders B on A.order_number=B.order_number
        where A.agent_id=".auth()->user()->agent_id."
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataTotalInvoice(){
        /*
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())
        */
        $data = DB::select("select count(A.id) as total from bills A
        join agents B on A.agent_id=B.id
        where A.agent_id=".auth()->user()->agent_id."
        and A.last_status <> 'Verified'");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getTotalOrderOnDelivery(){
        $data = DB::select("select count(A.id) as total from order_agents A
        join orders B on A.order_number=B.order_number
        where A.agent_id=".auth()->user()->agent_id."
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())
        and B.last_status <> 'Delivered'");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getTotalOrderDelivered(){
        $data = DB::select("select count(A.id) as total from order_agents A
        join orders B on A.order_number=B.order_number
        where A.agent_id=".auth()->user()->agent_id."
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())
        and B.last_status = 'Delivered'");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataOrderMonthly(){
        $data = DB::select("select date_format(B.pickup_date,'%Y-%m-%d') as date, 
        count(A.id) as total from order_agents A
        join orders B on A.order_number=B.order_number
        where A.agent_id=".auth()->user()->agent_id."
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())
        group by date_format(B.pickup_date,'%Y-%m-%d')
        order by date_format(B.pickup_date,'%Y-%m-%d')");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }

    private function getDataRecentOrder(){
        $data = DB::select("select B.awb_no, B.total_kg, B.last_status, 
        date_format(B.pickup_date, '%d-%M-%y') as date from order_agents A
        join orders B on A.order_number=B.order_number
        where A.agent_id=".auth()->user()->agent_id."
        and month(B.pickup_date) = month(curdate())
        and year(B.pickup_date) = year(curdate())
        order by B.awb_no desc limit 5");

        if (count($data) > 0) {
            return $data;
        }else{
            return NULL;
        }
    }
}