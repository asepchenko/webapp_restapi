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
use Exception;

use App\Repositories\BillRepository;
use App\Repositories\BillDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderCostAgentRepository;
use App\Repositories\OrderAgentDestinationRepository;
use App\Repositories\AgentMasterPriceRepository;

class BillDetailService extends BaseService
{
    protected $repo, $billService, $repoOrder, $repoOrderCostAgent, $repoCostAgent, $repoAgentPrice;

    public function __construct(
        BillRepository $billRepo,
        BillDetailRepository $repo,
        OrderRepository $repoOrder,
        OrderCostAgentRepository $repoOrderCostAgent,
        OrderAgentDestinationRepository $repoCostAgent,
        AgentMasterPriceRepository $repoAgentPrice
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->billRepo = $billRepo;
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

    public function show($id)
    {
        $data = $this->repo->show($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            /*$validator = Validator::make($data, [
                'billdetail_name' => 'required|max:255|unique:billdetails,billdetail_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            //$cost = $this->repoOrderCostAgent->getCostByAgent($request->order_number, $request->agent_id);
            $order = $this->repoOrder->getOrder($request->order_number);
            $tmp = $this->repoCostAgent->getDataByOrderNumberAgentOrigin($request->order_number, $request->agent_id);
        
            $cost = $this->repoAgentPrice->getMasterPriceRates($request->agent_id, $tmp->origin, $tmp->destination, $order[0]->service_id);
            
            if(!$cost){
                throw new exception('Data harga agent tidak ditemukan');
            }
            //throw new exception($cost);
            $total_kg_agent = $order[0]->total_kg_agent;
            //$total_kg_agent = str_replace(',','.',$total_kg_agent);
            $price = str_replace('.','',$cost->price);
            $price = str_replace(',','.',$price);

            $data['bill_number']     = $request->bill_number;
            $data['order_number']    = $request->order_number;
            $data['total_kg']        = $total_kg_agent;
            $data['price']           = $price;
            $data['subtotal']        = $price * $total_kg_agent;
            $data['user_id']         = auth()->user()->id;
            $result = $this->repo->create($data);

            //update bills table
            $order_number = $this->repo->getListOrderByInvoiceNumber($request->bill_number);
            $subtotal = $this->repo->getSumSubTotal($order_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogramAgent($order_number);

            //get data bill
            $bill = $this->billRepo->getDataByID($request->id);

            $grandtotal = str_replace('.','',$bill[0]->grand_total);
            $grandtotal = str_replace(',','.',$grandtotal);

            $discount = $bill[0]->discount_percent;

            $ppn = $bill[0]->income_tax_percent;
            $pph = $bill[0]->tax_percent;

            if($discount > 0){
                $disc = $subtotal * $discount / 100;
            }else{
                $disc = 0;
            }

            $dpp = $subtotal - $disc;

            if($ppn > 0){
                $income_tax = $dpp * $ppn / 100;
            }else{
                $income_tax = 0;
            }

            if($pph > 0){
                $tax = $dpp * $pph / 100;
            }else{
                $tax = 0;
            }

            if($grandtotal > 0){
                $grandtotal = $dpp + $income_tax;
            }

            $data_bill = [];
            $data_bill['income_tax']            = $income_tax;
            $data_bill['income_tax_percent']    = $ppn;
            $data_bill['tax']                   = $tax;
            $data_bill['tax_percent']           = $pph;
            $data_bill['discount']              = $disc;
            $data_bill['discount_percent']      = $discount;
            $data_bill['total_volume']          = $total_kg;
            $data_bill['total_colly']           = $total_colly;
            $data_bill['subtotal']              = $subtotal;
            $data_bill['grand_total']           = $grandtotal;
            $this->billRepo->update($data_bill, $request->id);

            DB::commit();
            return ResponseFormatter::success($result,'OK');
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
                'billdetail_name' => ['required','max:255',Rule::unique('billdetails', 'billdetail_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['user_id'] = auth()->user()->id;
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
        
            $tmp = $this->repo->getDataByID($id);
            $this->repo->delete($id);
            //update bills table
            $order_number = $this->repo->getListOrderByInvoiceNumber($tmp[0]->bill_number);
            $subtotal = $this->repo->getSumSubTotal($order_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogramAgent($order_number);

            //get data bill
            $bill = $this->billRepo->getDataByID($tmp[0]->id);

            $grandtotal = str_replace('.','',$bill[0]->grand_total);
            $grandtotal = str_replace(',','.',$grandtotal);

            $discount = $bill[0]->discount_percent;

            $ppn = $bill[0]->income_tax_percent;
            $pph = $bill[0]->tax_percent;

            if($discount > 0){
                $disc = $subtotal * $discount / 100;
            }else{
                $disc = 0;
            }

            $dpp = $subtotal - $disc;

            if($ppn > 0){
                $income_tax = $dpp * $ppn / 100;
            }else{
                $income_tax = 0;
            }

            if($pph > 0){
                $tax = $dpp * $pph / 100;
            }else{
                $tax = 0;
            }

            if($grandtotal > 0){
                $grandtotal = $dpp + $income_tax;
            }

            $data_bill = [];
            $data_bill['income_tax']            = $income_tax;
            $data_bill['income_tax_percent']    = $ppn;
            $data_bill['tax']                   = $tax;
            $data_bill['tax_percent']           = $pph;
            $data_bill['discount']              = $disc;
            $data_bill['discount_percent']      = $discount;
            $data_bill['total_volume']          = $total_kg;
            $data_bill['total_colly']           = $total_colly;
            $data_bill['subtotal']              = $subtotal;
            $data_bill['grand_total']           = $grandtotal;
            $this->billRepo->update($data_bill, $tmp[0]->id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
