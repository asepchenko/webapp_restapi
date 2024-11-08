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

use App\Repositories\BillRepository;
use App\Repositories\BillDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderCostAgentRepository;
use App\Repositories\OrderAgentDestinationRepository;
use App\Repositories\AgentMasterPriceRepository;

class BillService extends BaseService
{
    protected $repo, $repoDetail, $repoOrder, $repoOrderCostAgent, $repoCostAgent, $repoAgentPrice;

    public function __construct(
        BillRepository $repo,
        BillDetailRepository $repoDetail,
        OrderRepository $repoOrder,
        OrderCostAgentRepository $repoOrderCostAgent,
        OrderAgentDestinationRepository $repoCostAgent,
        AgentMasterPriceRepository $repoAgentPrice
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoDetail = $repoDetail;
        $this->repoOrder = $repoOrder;
        $this->repoOrderCostAgent = $repoOrderCostAgent;
        $this->repoCostAgent = $repoCostAgent;
        $this->repoAgentPrice = $repoAgentPrice;
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

    public function orderListByAgentID($agent_id)
    {
        $data = $this->repo->getOrderListByAgentID($agent_id);
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

    public function floor($number){
        //return sprintf('%.4f', floor($number*10000*($number>0?1:-1))/10000*($number>0?1:-1));
        return number_format((float)$number, 2, '.', '');
    }

    public function create(Request $request, array $data, $agent_id)
    {
        //throw new Exception($data['order_number']);
        //ResponseFormatter::success('test','OK');
        DB::beginTransaction();
        try {
            
            $order_number = explode(',', $data['order_number']);
            //validasi tidak boleh hanya 1 order/stt
            //disable for testing
            /*if(count($order_number) <=1){
                throw new Exception('Data Order STT yang akan dibuat manifest harus lebih dari 1');
            }*/

            //cek harus dari customer yg sama
            /*$cek = $this->repoOrder->isSameCustomer($order_number);
            if(!$cek){
                throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda customer !!');
            }else{
                $customer_id = $this->repoOrder->getCustomerID($order_number);
                //cek harus service yg sama
                $cek_groupservice = $this->repoOrder->isSameGroupService($order_number);
                if(!$cek_groupservice){
                    throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda group service !!');
                }

                //cek harus service yg sama
                $cek_service = $this->repoOrder->isSameService($order_number);
                if(!$cek_service){
                    throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda service/layanan !!');
                }
            }*/

            //get destination
            //$destination = $this->repoOrder->getSameDestination($order_number);

            //get total colly
            //$total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            /*$total_kg = $this->repoOrder->getSumKilogram($order_number);

            $subtotal = $this->repoOrderCost->getSumNett($order_number);
            $grandtotal = $this->repoOrderCost->getSumGrandTotal($order_number);
            $tax = $this->repoOrderCost->getSumTax($order_number);

            if($this->repoOrderCost->isSameTaxPercent($order_number)){
                $tax_percent = $this->repoOrderCost->getDistinctTaxPercent($order_number);
            }else{
                $tax_percent = 0;
            }*/

            //insert table
            /*$data['subtotal']         = $subtotal;
            $data['grand_total']      = $grandtotal;
            $data['total_colly']      = $total_colly;
            $data['total_volume']     = str_replace(',','',$total_kg);
            $data['tax_percent']      = $tax_percent;
            $data['tax']              = $tax;*/
            $data['bill_date']        = Carbon::now()->toDateTimeString();
            $data['agent_id']         = $agent_id;
            $data['last_status']      = "Draft";
            $data['user_id']          = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get invoice number
            $invoice_number = $this->repo->getInvoiceNumber($id);

            $data_detail = [];

            foreach ($order_number as $value) {
                $cost = $this->repoOrderCostAgent->getCostByAgent($value, $agent_id);
                //$tmp_cost = $this->repoCostAgent->getDataByOrderNumberAgentorigin($value, $agent_id);
                $order = $this->repoOrder->getOrder($value);

                //insert detail
                $total_kg_agent = $order[0]->total_kg_agent;
                //$total_kg_agent = str_replace(',','.',$total_kg_agent);
                $price = str_replace('.','',$cost[0]->price);
                $price = str_replace(',','.',$price);

                $data_detail['bill_number']     = $invoice_number;
                $data_detail['order_number']    = $value;
                $data_detail['total_kg']        = $total_kg_agent;
                $data_detail['price']           = $price;
                $data_detail['subtotal']        = $price * $total_kg_agent;
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

    public function createByAdmin(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            $data['last_status']      = "Admin";
            $data['user_id']          = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get invoice number
            $invoice_number = $this->repo->getInvoiceNumber($id);

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
                'bill_name' => ['required','max:255',Rule::unique('bills', 'bill_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            //$data['user_id'] = auth()->user()->id;
            
            $order_number = $this->repoDetail->getListOrderByInvoiceNumber($request->bill_number);
            $subtotal = $this->repoDetail->getSumSubTotal($order_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogramAgent($order_number);

            //throw new Exception($subtotal);
            //$bill = $this->repo->getDataByID($id);

            //$grandtotal = str_replace('.','',$bill[0]->grand_total);
            //$grandtotal = str_replace(',','.',$grandtotal);
            //throw new Exception($grandtotal);
            //$subtotal = str_replace('.','',$bill[0]->subtotal);
            //$subtotal = str_replace(',','.',$subtotal);

            if($request->discount == "" || $request->discount == "0"){
                $data['discount_percent'] = 0;
                $data['discount'] = 0;
                $discount = 0;
            }else{
                $data['discount_percent'] = $request->discount;
                $discount = $subtotal * $request->discount / 100;
                $data['discount'] = $discount;
            }

            $dpp = $subtotal - $discount;

            if($request->income_tax != "" && $request->income_tax != "0"){
                $income_tax = $dpp * $request->income_tax / 100;
                $data['income_tax'] = $income_tax;
                $data['income_tax_percent'] = $request->income_tax;
            }else{
                $income_tax = 0;
                $data['income_tax'] = $income_tax;
                $data['income_tax_percent'] = $income_tax;
            }

            if($request->tax != "" && $request->tax != "0"){
                $tax = $dpp * $request->tax / 100;
                $data['tax'] = $tax;
                $data['tax_percent'] = $request->tax;
            }else{
                $tax = 0;
                $data['tax'] = $tax;
                $data['tax_percent'] = $tax;
            }

            $grandtotal = $dpp + $income_tax; // - $income_tax - $discount;

            $data['total_volume']   = $total_kg;
            $data['total_colly']    = $total_colly;
            $data['subtotal']       = $subtotal;
            $data['grand_total']    = $grandtotal;
            $result = $this->repo->update($data, $id);
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
            if($this->repo->getDueDate($request->id) == "" || $this->repo->getDueDate($request->id) == null){
                throw new Exception('Due Date belum di set !!');
            }
            
            $data['last_status']        = 'Open';
            //$data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function pay(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $data['payment_date']       = $request->payment_date;
            $data['last_status']        = 'Verified';
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
