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

use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderCostRepository;

class InvoiceDetailService extends BaseService
{
    protected $repo;

    public function __construct(
        InvoiceDetailRepository $repo,
        InvoiceRepository $invoiceRepo,
        OrderRepository $repoOrder,
        OrderCostRepository $repoOrderCost
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->invoiceRepo = $invoiceRepo;
        $this->repoOrder = $repoOrder;
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

            $data['invoice_number']  = $request->invoice_number;
            $data['order_number']    = $request->order_number;
            $data['user_id']         = auth()->user()->id;
            $result = $this->repo->create($data);

            //update invoice table
            $order_number = $this->repo->getListOrderByInvoiceNumber($request->invoice_number);
            $cek_groupservice = $this->repoOrder->isSameGroupService($order_number);
            if(!$cek_groupservice){
                throw new Exception('Data Order STT yang akan dibuat invoice tidak boleh berbeda group service !!');
            }

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            $service_group_id = $this->repoOrder->getServiceGroupID($order_number);
            
            //cek apakah ini tipe trucking
            if($service_group_id == 3){
                $subtotal = $this->repoOrderCost->getSumPrice($order_number);
            }else{
                $subtotal = $this->repoOrderCost->getSumNett($order_number);
            }

            //throw new Exception($subtotal);
            $grandtotal = $subtotal;//$this->repoOrderCost->getSumGrandTotal($order_number);

            //get data invoice
            $inv = $this->invoiceRepo->getDataByID($request->id);

            //$grandtotal = str_replace('.','',$bill[0]->grand_total);
            //$grandtotal = str_replace(',','.',$grandtotal);

            $discount = $inv[0]->discount_percent;

            $ppn = $inv[0]->income_tax_percent;
            $pph = $inv[0]->tax_percent;

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

            $data_inv = [];
            $data_inv['income_tax']            = round($income_tax);
            $data_inv['income_tax_percent']    = $ppn;
            $data_inv['tax']                   = round($tax);
            $data_inv['tax_percent']           = $pph;
            $data_inv['discount']              = round($disc);
            $data_inv['discount_percent']      = $discount;
            $data_inv['total_volume']          = round($total_kg);
            $data_inv['total_colly']           = $total_colly;
            $data_inv['subtotal']              = round($subtotal);
            $data_inv['grand_total']           = round($grandtotal);
            $this->invoiceRepo->update($data_inv, $request->id);

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
                'invoicedetail_name' => ['required','max:255',Rule::unique('invoicedetails', 'invoicedetail_name')->ignore($id)]
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

            //update invoice table
            $order_number = $this->repo->getListOrderByInvoiceNumber($tmp[0]->invoice_number);
            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            $service_group_id = $this->repoOrder->getServiceGroupID($order_number);

            //cek apakah ini tipe trucking
            if($service_group_id == 3){
                $subtotal = $this->repoOrderCost->getSumPrice($order_number);
            }else{
                $subtotal = $this->repoOrderCost->getSumNett($order_number);
            }
            
            $grandtotal = $subtotal; //$this->repoOrderCost->getSumGrandTotal($order_number);

            //get data invoice
            $inv = $this->invoiceRepo->getDataByID($tmp[0]->id);

            //$grandtotal = str_replace('.','',$bill[0]->grand_total);
            //$grandtotal = str_replace(',','.',$grandtotal);

            $discount = $inv[0]->discount_percent;

            $ppn = $inv[0]->income_tax_percent;
            $pph = $inv[0]->tax_percent;

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

            $data_inv = [];
            $data_inv['income_tax']            = $income_tax;
            $data_inv['income_tax_percent']    = $ppn;
            $data_inv['tax']                   = $tax;
            $data_inv['tax_percent']           = $pph;
            $data_inv['discount']              = $disc;
            $data_inv['discount_percent']      = $discount;
            $data_inv['total_volume']          = $total_kg;
            $data_inv['total_colly']           = $total_colly;
            $data_inv['subtotal']              = $subtotal;
            $data_inv['grand_total']           = $grandtotal;
            $this->invoiceRepo->update($data_inv, $tmp[0]->id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
