<?php

namespace App\Http\Controllers\API\Customer;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReportTransactionController extends Controller
{
    protected $model;
    public function __construct(
        Order $model
    )
    {
        $this->model = $model;
    }

    public function getDeliveryStatus($month, $year)
    {
        $datas = DB::select("
        select A.order_number, A.awb_no, D.price_code, 
        concat(H.city_name, ' - ',I.city_name) as ori_dest,
        C.branch_name, 
        concat(F.group_name,' ', G.service_name) as service_name,
        A.pickup_date, A.delivered_date, E.min_days, E.max_days,
        datediff(A.delivered_date,A.pickup_date) as res,
        case
        when datediff(A.delivered_date,A.pickup_date) > E.max_days then 'Terlambat'
        else 'Tepat Waktu'
        end as stat
        from orders A
        join customers B on A.customer_id=B.id
        join customer_branchs C on A.customer_branch_id=C.id
        join customer_master_prices D on A.customer_master_price_id=D.id
        join locations E on D.price_code=E.price_code
        join service_groups F on A.service_group_id=F.id
        left join services G on A.service_id=G.id
        join cities H on A.origin=H.id
        join cities I on A.destination=I.id
        where A.customer_id=".auth()->user()->customer_id."
        and A.last_status='Delivered'
        and month(A.pickup_date) = ".$month."
        and year(A.pickup_date) = ".$year." order by A.pickup_date asc");
        return ResponseFormatter::success($datas,'OK');
    }
}