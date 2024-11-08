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

use App\Repositories\TruckingPriceRepository;

class TruckingPriceService extends BaseService
{
    protected $repo;

    public function __construct(
        TruckingPriceRepository $repo
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

    public function getTruckingPriceRates($origin, $destination, $type)
    {
        $data = $this->repo->getTruckingPriceRates($origin, $destination, $type);
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
                'truckingprice_name' => 'required|max:255|unique:truckingprices,truckingprice_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['user_id'] = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            DB::commit();
            return ResponseFormatter::success($id,'OK');
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
                'truckingprice_name' => ['required','max:255',Rule::unique('truckingprices', 'truckingprice_name')->ignore($id)]
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
