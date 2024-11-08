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

use App\Repositories\CogsRepository;
use App\Repositories\LocationRepository;
use App\Repositories\ServiceRepository;

class CogsService extends BaseService
{
    protected $repo;

    public function __construct(
        CogsRepository $repo,
        LocationRepository $locRepo,
        ServiceRepository $serviceRepo
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->locRepo = $locRepo;
        $this->serviceRepo = $serviceRepo;
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

            $loc = $this->locRepo->show($request->location_id);
            $service = $this->serviceRepo->show($request->service_id);

            $data['price_code'] = $loc->origins->city_code.$loc->destinations->city_code."_".$service->service_code;
            
            //validating
            /*$validator = Validator::make($data, [
                'price_code' => 'required|max:255|unique:cogs,cog_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->createWithID($data);

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
                'cog_name' => ['required','max:255',Rule::unique('cogs', 'cog_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['total_price'] = $request->price + $request->administrative_cost + $request->insurance_fee + $request->other_cost;
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
