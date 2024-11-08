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

use App\Repositories\OrderRepository;
use App\Repositories\OrderUnitRepository;

class OrderUnitService extends BaseService
{
    protected $repo, $repoOrder;

    public function __construct(
        OrderUnitRepository $repo,
        OrderRepository $repoOrder
    ) {
        parent::__construct();
        $this->repo = $repo;
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

    public function indexByOrderID($id)
    {
        $data = $this->repo->getIndexByOrderID($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','200');
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
                'orderunit_name' => 'required|max:255|unique:orderunits,orderunit_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $colly = $this->repo->getSumColly($request->order_number);
            $total = $colly + $request->colly;
            $order_colly = $this->repoOrder->show($request->order_number);

            if($total > $order_colly->total_colly){
                throw new Exception('Total colly tidak boleh melebihi '.$order_colly->total_colly);
            }

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->create($data);

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
                'orderunit_name' => ['required','max:255',Rule::unique('orderunits', 'orderunit_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            $colly = $this->repo->getSumCollyExceptID($request->order_number, $id);
            $total = $colly + $request->colly;
            $order_colly = $this->repoOrder->show($request->order_number);

            if($total > $order_colly->total_colly){
                throw new Exception('Total colly tidak boleh melebihi '.$order_colly->total_colly);
            }

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
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
