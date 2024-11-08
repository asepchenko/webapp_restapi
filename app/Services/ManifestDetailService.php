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

use App\Repositories\ManifestRepository;
use App\Repositories\ManifestDetailRepository;
use App\Repositories\OrderRepository;

class ManifestDetailService extends BaseService
{
    protected $repo, $repoOrder;

    public function __construct(
        ManifestDetailRepository $repo,
        ManifestRepository $repoManifest,
        OrderRepository $repoOrder
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoManifest = $repoManifest;
        $this->repoOrder = $repoOrder;
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
                'manifestdetail_name' => 'required|max:255|unique:manifestdetails,manifestdetail_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->create($data);

            //update to table manifests
            //get list orders
            $order_number = $this->repo->getArrayListOrderByOneManifestNumber($request->manifest_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            $data_manifest = [];
            $data_manifest['total_colly']    = $total_colly;
            $data_manifest['total_kg']       = str_replace(',','',$total_kg);
            $data_manifest['total_order']    = count($order_number);
            $this->repoManifest->updateByManifestNumber($data_manifest, $request->manifest_number);

            foreach ($order_number as $value) {
                //update all orders
                $data_order = [];
                $data_order['last_status']     = "Warehouse";
                $data_order['user_id']         = auth()->user()->id;
                $this->repoOrder->updateByOrderNumber($data_order, $value);
            }

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
                'manifestdetail_name' => ['required','max:255',Rule::unique('manifestdetails', 'manifestdetail_name')->ignore($id)]
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
            $manifest_number = $this->repo->getManifestNumber($id);
            $ord_number = $this->repo->getOrderNumber($id);

            //update order
            $data_order = [];
            $data_order['last_status']     = "Closing";
            $data_order['user_id']         = auth()->user()->id;
            $this->repoOrder->updateByOrderNumber($data_order, $ord_number);

            $this->repo->delete($id);
            //update to table manifests
            //get list orders
            $order_number = $this->repo->getArrayListOrderByOneManifestNumber($manifest_number);

            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            $data_manifest = [];
            $data_manifest['total_colly']    = $total_colly;
            $data_manifest['total_kg']       = str_replace(',','',$total_kg);
            $data_manifest['total_order']    = count($order_number);
            $this->repoManifest->updateByManifestNumber($data_manifest, $manifest_number);

            /*foreach ($order_number as $value) {
                //update all orders
                $data_order = [];
                $data_order['last_status']     = "Closing";
                $data_order['user_id']         = auth()->user()->id;
                $this->repoOrder->updateByOrderNumber($data_order, $value);
            }*/

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
