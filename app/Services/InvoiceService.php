<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;
use Exception;

use App\Repositories\CustomerRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceDetailRepository;
use App\Repositories\InvoiceApprovalRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderCostRepository;

class InvoiceService extends BaseService
{
    protected $repo, $repoOrder, $repoDetail, $repoCust, $repoOrderCost, $repoApproval;

    public function __construct(
        InvoiceRepository $repo,
        InvoiceDetailRepository $repoDetail,
        InvoiceApprovalRepository $repoApproval,
        OrderRepository $repoOrder,
        CustomerRepository $repoCust,
        OrderCostRepository $repoOrderCost
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoDetail = $repoDetail;
        $this->repoApproval = $repoApproval;
        $this->repoOrder = $repoOrder;
        $this->repoCust = $repoCust;
        $this->repoOrderCost = $repoOrderCost;
    }

    public function index(Request $request)
    {
        $data = $this->repo->getIndex($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function orderList(Request $request)
    {
        $data = $this->repo->getOrderList($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function orderListByCustomerID($customer_id)
    {
        $data = $this->repo->getOrderListByCustomerID($customer_id);
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

    public function formatDec($number){
        //return sprintf('%.4f', floor($number*10000*($number>0?1:-1))/10000*($number>0?1:-1));
        $temp = str_replace('.','',$number);
        $temp = str_replace(',','.',$temp);
        return $temp;
    }

    public function floor($number){
        //return sprintf('%.4f', floor($number*10000*($number>0?1:-1))/10000*($number>0?1:-1));
        return number_format((float)$number, 2, '.', '');
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $order_number = explode(',', $request->order_number);
            //validasi tidak boleh hanya 1 order/stt
            //disable for testing
            /*if(count($order_number) <=1){
                throw new Exception('Data Order STT yang akan dibuat manifest harus lebih dari 1');
            }*/

            //cek harus dari customer yg sama
            $cek = $this->repoOrder->isSameCustomer($order_number);
            if(!$cek){
                throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda customer !!');
            }else{
                $customer_id = $this->repoOrder->getCustomerID($order_number);
                //cek harus service yg sama
                $cek_groupservice = $this->repoOrder->isSameGroupService($order_number);
                if(!$cek_groupservice){
                    throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda group service !!');
                }

                $service_group_id = $this->repoOrder->getServiceGroupID($order_number);

                //cek harus service yg sama
                /*$cek_service = $this->repoOrder->isSameService($order_number);
                if(!$cek_service){
                    throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda service/layanan !!');
                }*/
            }

            //get destination
            //$destination = $this->repoOrder->getSameDestination($order_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            //cek apakah ini tipe trucking
            if($service_group_id == 3){
                $subtotal = $this->repoOrderCost->getSumPrice($order_number);
            }else{
                $subtotal = $this->repoOrderCost->getSumNett($order_number);
            }
            //throw new exception($subtotal);
            $grandtotal = $subtotal; //$this->repoOrderCost->getSumGrandTotal($order_number);
            //$tax = $this->repoOrderCost->getSumTax($order_number);

            /*if($this->repoOrderCost->isSameTaxPercent($order_number)){
                $tax_percent = $this->repoOrderCost->getDistinctTaxPercent($order_number);
            }else{
                $tax_percent = 0;
            }*/

            //insert table
            $data['subtotal']           = round($subtotal);
            $data['grand_total']        = round($grandtotal);
            $data['total_colly']        = $total_colly;
            $data['total_volume']       = str_replace(',','',$total_kg);
            //$data['tax_percent']      = $tax_percent;
            //$data['tax']              = $tax;
            $data['invoice_date']       = Carbon::now()->toDateTimeString();
            $data['customer_id']        = $customer_id;
            $data['approval_user_id']   = $this->repoApproval->getApprovalUser();
            $data['service_group_id']   = $service_group_id;
            $data['last_status']        = "Draft";
            $data['user_id']            = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get invoice number
            $invoice_number = $this->repo->getInvoiceNumber($id);

            $data_detail = [];

            foreach ($order_number as $value) {
                
                //insert detail
                $data_detail['invoice_number']  = $invoice_number;
                $data_detail['order_number']    = $value;
                $data_detail['user_id']         = auth()->user()->id;
                $this->repoDetail->create($data_detail);
            }

            DB::commit();
            return ResponseFormatter::success($invoice_number,'OK');
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
            /*$validator = Validator::make($data, [
                'invoice_name' => ['required','max:255',Rule::unique('invoices', 'invoice_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            $order_number = [];
            $order_number = $this->repoDetail->getListOrderByInvoiceNumber($request->invoice_number);
            //$order_number = implode($order_number);
            //$order_number = json_encode(array_column($order_number,'order_number'));
            //throw new Exception($order_number);

            $order_number = array_column($order_number,'order_number');
            $service_group_id = $this->repoOrder->getServiceGroupID($order_number);

            //cek apakah ini tipe trucking
            if($service_group_id == 3){
                $subtotal = $this->repoOrderCost->getSumPrice($order_number);
            }else{
                $subtotal = $this->repoOrderCost->getSumNett($order_number);
            }
            
            //throw new Exception($subtotal);

            $grandtotal = $subtotal; //$this->repoOrderCost->getSumGrandTotal($order_number);
            $tax = $this->repoOrderCost->getSumTax($order_number);

                if($request->is_disc_percent == "Y"){ 
                    $data['is_disc_percent'] = "Y";
                    if($request->discount_percent == "" || $request->discount_percent == "0"){
                        $data['discount_percent'] = 0;
                        $data['discount'] = 0;
                        $discount = 0;
                    }else{
                        $data['discount_percent'] = $request->discount_percent;
                        $discount = $subtotal * $request->discount_percent / 100;
                        $data['discount'] = $discount;
                    }
                }else{
                    $data['is_disc_percent']    = "N";
                    $data['discount_percent']   = 0;
                    $discount = $request->discount;
                    $data['discount'] = $discount;
                }

            $dpp = $subtotal - $discount;

            if($request->income_tax != "" && $request->income_tax != "0"){
                $income_tax = $dpp * $request->income_tax / 100;
                $data['income_tax'] = round($income_tax);
                $data['income_tax_percent'] = $request->income_tax;
            }else{
                $income_tax = 0;
                $data['income_tax'] = round($income_tax);
                $data['income_tax_percent'] = $income_tax;
            }

            if($request->tax != "" && $request->tax != "0"){
                $tax = $dpp * $request->tax / 100;
                $data['tax'] = round($tax);
                $data['tax_percent'] = $request->tax;
            }else{
                $tax = 0;
                $data['tax'] = round($tax);
                $data['tax_percent'] = $tax;
            }

            $grandtotal = $dpp + $income_tax; // - $income_tax - $discount;

            $data['termin']         = $request->termin;
            $data['subtotal']       = round($subtotal);
            $data['grand_total']    = round($grandtotal);
            $data['user_id']        = auth()->user()->id;
            $result = $this->repo->update($data, $id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function payment(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //validating
            $validator = Validator::make($data, [
                'filename' => 'file|mimes:jpg,jpeg,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //file upload processing
            if(isset($request->filename)){
                $timestamp = time();
                $ext = $request->filename->getClientOriginalExtension();
                $ori_file = $request->filename->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->filename->storeAs('invoice', $fileName);
                $data['filename'] = $fileName;
            }

            $data['payment_date']   = $request->payment_date;
            $data['last_status']    = "Payment";
            $result = $this->repo->update($data, $request->id);
            DB::commit();

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
            //$invoice_number = $this->repo->getInvoiceNumber($id);
            $last_status = $this->repo->getLastStatusByInvoiceNumber($id);

            if($last_status == "Draft"){
                $this->repoDetail->deleteByInvoiceNumber($id);
                $this->repo->deleteByInvoiceNumber($id);
            }else{
                throw new Exception('Invoice tidak dapat dihapus karena status sudah berubah !!');
            }

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function closing(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //cek due date is null
            //if($this->repo->getDueDate($request->id) == "" || $this->repo->getDueDate($request->id) == null){
            //    throw new Exception('Due Date belum di set !!');
            //}
            
            $data['send_date']          = Carbon::now()->toDateTimeString();
            $data['last_status']        = 'Sent';
            $data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function verify(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $data['verified_date']      = Carbon::now()->toDateTimeString();
            $data['last_status']        = 'Close';
            $data['verified_user_id']   = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function accept(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            $termin = $this->repo->getTermin($request->id);
            if(isset($request->received_date)){
                $received_date = $request->received_date;
                $data['due_date']           = Carbon::createFromFormat('Y-m-d',$received_date)->addDays($termin)->toDateTimeString();
            }else{
                $received_date = Carbon::now()->toDateTimeString();
                $data['due_date']           = Carbon::now()->addDays($termin)->toDateTimeString();
            }

            $data['received_date']      = $received_date;
            $data['last_status']        = 'Process';
            $data['verified_user_id']   = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
