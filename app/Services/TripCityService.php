<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
//use App\Services\TripService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\TripRepository;
use App\Repositories\TripCityRepository;

class TripCityService extends BaseService
{
    protected $repo, $repoTrip, $tripService;

    public function __construct(
        TripCityRepository $repo,
        TripRepository $repoTrip
        //TripService $tripService
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoTrip = $repoTrip;
        //$this->tripService = $tripService;
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

    public function indexByTripNumber($no)
    {
        $data = $this->repo->getByTripNumber($no);
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

    public function calc(array $param, $id, $state){
        try {
            $data = [];
            $data['user_id']               = auth()->user()->id;

            //cleansing format
            $mlt_number         = str_replace(',','',$param['multiplier_number']);
            $trip = $this->repoTrip->getDataTripByID($param['trip_id']);
            $trip_mlt_number    = str_replace('.','',$trip[0]->multiplier_number);
            $trip_mlt_number    = str_replace(',','.',$trip_mlt_number);

            $trip_ops_cost      = str_replace('.','',$trip[0]->operational_cost);
            $trip_ops_cost      = str_replace(',','.',$trip_ops_cost);
            $trip_number        = $trip[0]->trip_number;

            if($state == "first"){
                $data['multiplier_number']     = $mlt_number;
                $data['cogs_kg']               = $this->floor($trip_mlt_number * $mlt_number);

                //first update
                $this->repo->update($data, $id);
            }else{

            //calc hpp
            $sum_kg = $this->repo->getSumKilogram($trip_number);
            $avg_ops_cost =  $this->floor($trip_ops_cost / $sum_kg);

            $data['avg_ops_cost']       = $avg_ops_cost;

            //total all cogs_kg / count city or manifest
            $sum_cogs_kg = $this->repo->getSumCogsKgByTrip($trip_number);
            //throw new exception($sum_cogs_kg);
            $count_mft = $this->repo->getCountDataByTrip($trip_number);
            $cogs_avg = $this->floor($sum_cogs_kg / $count_mft);
            $data['cogs_avg']           = $cogs_avg;

            $tmp_cogs = $this->repo->getDataByID($id);
            $cogs_kg = str_replace('.','',$tmp_cogs[0]->cogs_kg);
            $cogs_kg = str_replace(',','.',$cogs_kg);
            $tmp = $this->floor($cogs_kg - $cogs_avg);
                
            $cogs_real_kg = $tmp + $avg_ops_cost;
            $data['cogs_real_kg']       = $cogs_real_kg;

            $kg = str_replace('.','',$tmp_cogs[0]->kg);
            $kg = str_replace(',','.',$kg);
            $data['cogs_real_city']     = $this->floor($cogs_real_kg * $kg);

            //last update
            $this->repo->update($data, $id);
            }
            return 'OK';
        } catch (Exception $exc) {
            return $exc;
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
                'tripcity_name' => 'required|max:255|unique:tripcities,tripcity_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
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
                'tripcity_name' => ['required','max:255',Rule::unique('tripcities', 'tripcity_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);

            /*$param = [];
            $param['multiplier_number'] = $request->multiplier_number;
            $param['trip_id']           = $request->trip_id;
            $res = $this->calc($param, $id, 'next');
            if($res != 'OK'){
                throw new Exception($res);
            }*/

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
