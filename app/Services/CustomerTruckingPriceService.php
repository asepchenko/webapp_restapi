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

use App\Repositories\CustomerTruckingPriceRepository;

class CustomerTruckingPriceService extends BaseService
{
    protected $repo;

    public function __construct(
        CustomerTruckingPriceRepository $repo
    ) {
        parent::__construct();
        $this->repo = $repo;
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

    public function indexPending(Request $request)
    {
        $data = $this->repo->getIndexPending($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function indexByCustomerID($id)
    {
        $data = $this->repo->getIndexByCustomerID($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }
    
    public function getTruckingPriceRates($customer, $origin, $destination, $truck)
    {
        $data = $this->repo->getTruckingPriceRates($customer, $origin, $destination, $truck);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','200');
        }
    }

    public function show($id)
    {
        if ($id == 0) {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
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
                'customermasterprice_name' => 'required|max:255|unique:customermasterprices,customermasterprice_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            //check if already exist
            $check = $this->repo->checkAlreadyExist($request->customer_id, $request->price_code, $request->truck_type_id);

            if($check){
                throw new Exception('Master harga trucking ini sudah tersedia');
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
                'customermasterprice_name' => ['required','max:255',Rule::unique('customermasterprices', 'customermasterprice_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['status'] = 'PENDING';
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function approve(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $data['status'] = 'APPROVED';
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function massApprove(Request $request)
    {
        DB::beginTransaction();
        try {
            $ids = explode(',', $request->ids);
            foreach ($ids as $value) {
                $data = [];
                //insert detail
                $data['status'] = 'APPROVED';
                $data['approved_at'] = now();
                $data['approved_by'] = auth()->user()->id;
                $result = $this->repo->update($data, $value);
            }

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
            //check if data has been used in orders
            $check = $this->repo->checkIsHasUsed($id);

            if($check){
                throw new Exception('Master harga ini sudah pernah digunakan, tidak bisa dihapus');
            }

            //update first
            $data['deleted_by'] = auth()->user()->id;
            $this->repo->update($data, $id);
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
