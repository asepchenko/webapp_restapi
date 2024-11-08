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

use App\Repositories\AgentMasterPriceRepository;
use App\Repositories\LocationRepository;
use App\Repositories\ServiceRepository;

class AgentMasterPriceService extends BaseService
{
    protected $repo;

    public function __construct(
        AgentMasterPriceRepository $repo,
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

    public function indexByAgentID($id)
    {
        $data = $this->repo->getIndexByAgentID($id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function getMasterPriceRates($agent, $origin, $destination, $service)
    {
        $data = $this->repo->getMasterPriceRates($agent, $origin, $destination, $service);
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
                'agentmasterprice_name' => 'required|max:255|unique:agentmasterprices,agentmasterprice_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $loc = $this->locRepo->show($request->location_id);
            $service = $this->serviceRepo->show($request->service_id);
            $price_code = $loc->origins->city_code.$loc->destinations->city_code."_".$service->service_code;
            
            $data['price_code'] = $price_code;
            //check if location id & service id already exist
            $check = $this->repo->checkAlreadyExist($request->agent_id, $price_code);

            if($check){
                throw new Exception('Master harga ini sudah tersedia');
            }

            $data['margin']     = 0;
            $data['user_id']    = auth()->user()->id;
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
                'agentmasterprice_name' => ['required','max:255',Rule::unique('agentmasterprices', 'agentmasterprice_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $loc = $this->locRepo->show($request->location_id);
            $service = $this->serviceRepo->show($request->service_id);

            $data['price_code'] = $loc->origins->city_code.$loc->destinations->city_code."_".$service->service_code;
            $data['user_id']    = auth()->user()->id;
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
