<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;
use Exception;

use App\Repositories\CustomerRepository;
use App\Repositories\CustomerMouRepository;
use App\Repositories\OrderAgentRepository;
use App\Repositories\OrderReferenceRepository;
use App\Repositories\OrderTrackingRepository;
use App\Repositories\OrderCostRepository;
use App\Repositories\OrderUnitRepository;
use App\Repositories\OrderRepository;

class OrderService extends BaseService
{
    protected $repo;

    public function __construct(
        OrderRepository $repo,
        OrderCostRepository $orderCostRepo,
        OrderUnitRepository $orderUnitRepo,
        OrderAgentRepository $orderAgentRepo,
        OrderReferenceRepository $orderRefRepo,
        OrderTrackingRepository $orderTrackingRepo,
        CustomerRepository $custRepo,
        CustomerMouRepository $custMouRepo
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->orderCostRepo = $orderCostRepo;
        $this->orderUnitRepo = $orderUnitRepo;
        $this->orderAgentRepo = $orderAgentRepo;
        $this->orderRefRepo = $orderRefRepo;
        $this->orderTrackingRepo = $orderTrackingRepo;
        $this->custRepo = $custRepo;
        $this->custMouRepo = $custMouRepo;
    }

    public function index(Request $request)
    {
        //dd(auth()->user()->branch_id);
        $data = $this->repo->getIndex($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function indexByDate($start_date, $end_date)
    {
        //dd(auth()->user()->branch_id);
        $data = $this->repo->getIndexByDate($start_date, $end_date);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function list(Request $request)
    {
        $data = $this->repo->getList($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function listByUserID(Request $request)
    {
        $data = $this->repo->getListByUserID($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function show($id)
    {
        $data = $this->repo->show($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $data_cost = [];
            //check mou customer first
            $mou = $this->custMouRepo->getActiveCountDataByCustID($request->customer_id);
            if ($mou <= 0) {
                return ResponseFormatter::error('','Customer ini tidak memiliki MOU Aktif','400');
            }
            //validating
            //max:64
            $validator = Validator::make($request->all(), [
                'awb_no' => 'required|numeric|unique:orders,awb_no'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //cleansing format
            $price          = str_replace(',','',$request->price);
            $total_kg       = str_replace(',','',$request->total_kg);
            $cogs_price     = str_replace(',','',$request->cogs_price);

            //save to orders
            $data['branch_id']          = auth()->user()->branch_id;
            $data['pickup_date']        = $request->pickup_date;
            $data['awb_no']             = $request->awb_no;
            $data['customer_id']        = $request->customer_id;
            $data['customer_branch_id'] = $request->customer_branch_id;
            $data['customer_master_price_id']   = $request->customer_master_price_id;
            $data['trucking_price_id']  = $request->trucking_price_id;
            $data['service_id']         = $request->service_id;
            $data['service_group_id']   = $request->service_group_id;
            $data['truck_type_id']      = $request->truck_type_id;
            $data['origin']             = $request->origin;
            $data['destination']        = $request->destination;
            $data['payment_type']       = $request->payment_type;
            $data['total_colly']        = $request->total_colly;
            $data['total_kg']           = $total_kg;
            $data['total_kg_agent']     = $total_kg;
            $data['last_status']        = 'Open';
            $data['user_id']            = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get order number
            $order_number = $this->repo->getOrderNumber($id);

            //save to orders costs
            $data_cost['order_number']      = $order_number;
            $data_cost['price']             = $price;
            $data_cost['cogs_price']        = $cogs_price;

            $tmp_cust = $this->custRepo->show($request->customer_id);
            
            $data_cost['nett']              = $price;

            //if($tmp_cust->tax == "Y"){
            //    $data_cost['tax_percent']       = 10;
            //    $tax = ($price * 10 / 100);
            //    $data_cost['tax']               = $tax;
            //    $data_cost['nett_with_tax']     = $price + $tax;
            //}else{
                $data_cost['tax_percent']       = 0;
                $data_cost['tax']               = 0;
                //$data_cost['nett_with_tax']     = $price;
                //$tax = 0;
            //}
            //$data_cost['grand_total']       = $price + $tax;
            $data_cost['user_id']           = auth()->user()->id;
            $this->orderCostRepo->create($data_cost);

            //save to orders unit
            /*$data_unit['order_number']      = $order_number;
            $data_unit['weight_type']       = $request->weight_type;
            $data_unit['height']            = $request->height;
            $data_unit['width']             = $request->width;
            $data_unit['length']            = $request->length;
            $data_unit['total_colly']       = $request->total_colly;
            $data_unit['kilogram']          = $request->kilogram;
            $data_unit['volume']            = $request->volume;
            $data_unit['user_id']           = auth()->user()->id;
            $this->orderUnitRepo->create($data_unit);*/

            //insert tracking orders
            $data_tracking['status_date']     = Carbon::now()->toDateTimeString();
            $data_tracking['status_name']     = 'On Process Delivery';
            $data_tracking['is_admin_view']   = 0;
            $data_tracking['city_id']         = $request->origin;
            $data_tracking['order_number']    = $order_number;
            $data_tracking['user_id']         = auth()->user()->id;
            $this->orderTrackingRepo->create($data_tracking);

            DB::commit();
            return ResponseFormatter::success($order_number,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {
            //validating
            $validator = Validator::make($data, [
                'awb_no' => ['required','numeric',Rule::unique('orders', 'awb_no')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //cleansing format
            $price          = str_replace(',','',$request->price);
            $total_kg       = str_replace(',','',$request->total_kg);
            $cogs_price     = str_replace(',','',$request->cogs_price);
            $packing_cost   = str_replace(',','',$request->packing_cost);
            $insurance_fee  = str_replace(',','',$request->insurance_fee);

            $data = [];
            $data_cost = [];

            //update to orders
            //$data['branch_id']          = auth()->user()->branch_id;
            //$data['order_date']         = $request->order_date;
            $data['awb_no']             = $request->awb_no;
            //$data['customer_id']        = $request->customer_id;
            //$data['customer_branch_id'] = $request->customer_branch_id;
            //$data['customer_master_price_id']   = $request->customer_master_price_id;
            //$data['service_id']         = $request->service_id;
            //$data['origin']             = $request->origin;
            //$data['destination']        = $request->destination;
            //$data['total_colly']        = $request->total_colly;
            //$data['delivered_date']     = $request->delivered_date;
            //$data['service_group_id']   = $request->service_group_id;
            //$data['truck_type_id']      = $request->truck_type_id;
            $data['payment_type']       = $request->payment_type;
            $data['total_colly']        = $request->total_colly;
            $data['total_kg']           = $total_kg;
            $data['description']        = $request->description;
            $data['contains']           = $request->contains;
            //$data['last_status']        = 'Open';
            $data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $id);

            //update to orders costs
            $data_cost['price']             = $price;
            $data_cost['cogs_price']        = $cogs_price;
            //$data_cost['discount']          = $request->discount; //percent
            $data_cost['packing_cost']      = $packing_cost;
            $data_cost['insurance_fee']     = $insurance_fee;
            
            /*$tmp_total = ($price + $packing_cost + $insurance_fee);
            if($request->discount > 0){
                $tmp_discount = ($tmp_total * $request->discount / 100);
            }else{
                $tmp_discount = 0;
            }*/
            //$nett = $tmp_total - $tmp_discount;
            //$grandtotal = $tmp_total - $tmp_discount;

            //$data_cost['nett']              = $nett;
            //$data_cost['tax_percent']       = $request->tax;
            //$tax = ($nett * $request->tax / 100);
            //$data_cost['tax']               = $tax;
            //$data_cost['nett_with_tax']     = $nett + $tax;
            
            //$data_cost['commission']        = $request->total_colly;
            //$data_cost['gross_margin']      = $request->total_colly;
            //$data_cost['gross_total']       = $tmp_total;

            //$data_cost['grand_total']       = $grandtotal; //$nett + $tax;
            $data_cost['user_id']           = auth()->user()->id;
            $this->orderCostRepo->updateByOrderNumber($data_cost, $request->order_number);

            //update to orders unit
            /*$data_unit['weight_type']       = $request->weight_type;
            $data_unit['height']            = $request->height;
            $data_unit['width']             = $request->width;
            $data_unit['length']            = $request->length;

            //$data_unit['volume']            = $request->volume;
            $data_unit['user_id']           = auth()->user()->id;
            $this->orderUnitRepo->updateByOrderNumber($data_unit, $request->order_number);*/

            DB::commit();
            //DB::rollBack();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            //check status first
            $last_status = $this->repo->getLastStatusByOrderNumber($id);

            if($last_status == "Open"){

                //delete all detail and reference
                $this->orderCostRepo->deleteByOrderNumber($id);
                $this->orderUnitRepo->deleteByOrderNumber($id);
                $this->orderAgentRepo->deleteByOrderNumber($id);
                $this->orderRefRepo->deleteByOrderNumber($id);
                $this->orderTrackingRepo->deleteByOrderNumber($id);

                $this->repo->deleteByOrderNumber($id);

                DB::commit();
                return ResponseFormatter::success('','OK');
            }else{
                throw new Exception("Order ".$id." tidak bisa dihapus karena sudah diproses");
            }
            
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function closing(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //check if there ref colly
            $ref_colly = $this->orderUnitRepo->getSumColly($request->order_number);
            $order_colly = $this->repo->show($request->order_number);
            if($ref_colly > 0){
                if($ref_colly <> $order_colly->total_colly){
                    throw new Exception('Total referensi colly tidak boleh kurang/lebih '.$order_colly->total_colly);
                }    
            }

            $volume = $this->orderUnitRepo->getSumVolume($request->order_number);
            if($volume > $order_colly->total_kg){
                $data['total_kg']        = $volume;
            }

            $data['last_status']        = 'Closing';
            $data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
