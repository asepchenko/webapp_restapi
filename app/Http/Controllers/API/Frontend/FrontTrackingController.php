<?php

namespace App\Http\Controllers\API\Frontend;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReference;
use App\Models\OrderTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontTrackingController extends Controller
{
    protected $model, $modelRef;
    public function __construct(
        Order $model,
        OrderReference $modelRef,
        OrderTracking $modelStatus)
    {
        $this->model = $model;
        $this->modelRef = $modelRef;
        $this->modelStatus = $modelStatus;
    }

    public function index(Request $request)
    {
        $datas = $this->model->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function tracking($no_awb)
    {
        $order = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','trucking_price')
                            ->where('awb_no',$no_awb)->get();
        
        /*$order = DB::select("select order_number from orders where awb_no='".$no_awb."'");*/
        if(count($order) > 0){
            $ref = $this->modelRef->where('order_number',$order[0]->order_number)->get();
            $status = $this->modelStatus->with('cities')->where('order_number',$order[0]->order_number)
                                        ->orderBy('status_date','desc')->get();
            $data = [
                'order' => $order,
                'ref' => $ref,
                'status' => $status
            ];
            return ResponseFormatter::success($data,'OK');
        }else{
            $ref = $this->modelRef->where('reference_number',$no_awb)->get();
            if(count($ref) > 0){
                $order = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','trucking_price')
                                    ->where('order_number',$ref[0]->order_number)->get();
                $status = $this->modelStatus->with('cities')->where('order_number',$ref[0]->order_number)
                                            ->orderBy('status_date','desc')->get();
                $data = [
                    'order' => $order,
                    'ref' => $ref,
                    'status' => $status
                ];
                return ResponseFormatter::success($data,'OK');
            }else{
                return ResponseFormatter::error('','Data Not Found','404');
            }
        }
    }
}