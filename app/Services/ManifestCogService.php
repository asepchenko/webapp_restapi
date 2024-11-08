<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Services\TripCityService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\ManifestCogRepository;
use App\Repositories\TripRepository;
use App\Repositories\TripDetailRepository;
use App\Repositories\TripCityRepository;
use App\Repositories\ManifestRepository;
use App\Repositories\ManifestDetailRepository;
use App\Repositories\OrderRepository;

class ManifestCogService extends BaseService
{
    protected $repo;

    public function __construct(
        ManifestCogRepository $repo,
        ManifestDetailRepository $repoManifestDetail,
        TripRepository $repoTrip,
        TripDetailRepository $repoDetail,
        TripCityService $tripCityService,
        TripCityRepository $repoCity,
        OrderRepository $repoOrder
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoTrip = $repoTrip;
        $this->repoCity = $repoCity;
        $this->tripCityService = $tripCityService;
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

    public function indexByTripNumber($no)
    {
        $data = $this->repo->getByTripNumber($no);
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
            return ResponseFormatter::error($data,'Data Not Found','200');
        }
    }

    public function floor($number){
        //return sprintf('%.4f', floor($number*10000*($number>0?1:-1))/10000*($number>0?1:-1));
        return number_format((float)$number, 2, '.', '');
    }

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            /*$validator = Validator::make($data, [
                'manifestcog_name' => 'required|max:255|unique:manifestcogs,manifestcog_name'
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

                //update trip city
                $data_city_cogs = [];
                $manifest_number = $this->repoDetail->getListManifestNumber($trip[0]->trip_number);
                $arr_order_number = $this->repoManifestDetail->getArrayListOrderByManifestNumber($manifest_number);
                $arr_city = $this->repoOrder->sumDataByOrderNumberCityID($arr_order_number);
                //hitung ulang cogs/hpp part 1
                foreach ($arr_city as $city) {
                    $tmp = $this->repoCity->getDataByTripNumberAndCityID($trip[0]->trip_number, $city->city_id);
                    $data_city_cogs['multiplier_number']     = $mlt_num;
                    $data_city_cogs['cogs_kg']               = $this->floor($trip_mlt_number * $mlt_number);
        
                    $data_city_cogs['user_id']               = auth()->user()->id;
                    $this->repoCity->updateByTripNumberAndCityID($data_city_cogs, $trip[0]->trip_number, $city->city_id);

                    //calc hpp
                    $param = [];
                    $param['multiplier_number'] = $mlt->num;
                    $param['trip_id']           = $trip[0]->id;
                    $this->tripCityService->calc($param, $tmp[0]->id, 'first');
                }

                //hitung ulang cogs/hpp part 2
                foreach ($arr_city as $city) {
                    $tmp = $this->repoCity->getDataByTripNumberAndCityID($trip[0]->trip_number, $city->city_id);
                    //$tmp = $this->repoManifestCogs->getDataByManifestNumber($value['manifest_number']);
                    //calc hpp
                    $param = [];
                    $param['multiplier_number'] = $tmp[0]->multiplier_number;
                    $param['trip_id']           = $trip[0]->id;
                    $this->tripCityService->calc($param, $tmp[0]->id, 'first');
                }
            }

            //calc hpp
            $sum_kg = $this->repo->getSumKilogram($trip_number);
            $avg_ops_cost =  $this->floor($trip_ops_cost / $sum_kg);

            $data['avg_ops_cost']       = $avg_ops_cost;

            //total all cogs_kg / count city or manifest
            $sum_cogs_kg = $this->repo->getSumCogsKgByTrip($trip_number);

            $count_mft = $this->repo->getCountManifestByTrip($trip_number);
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
            //return 'OK';
        } catch (Exception $exc) {
            return $exc;
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {

            $result = $this->calc($data, $id, 'first');

            if($result == NULL){
                //looping for other datas
                $trip = $this->repoTrip->getDataTripByID($request->trip_id);
                $ids = $this->repo->getOtherId($trip[0]->trip_number, $id);
                
                foreach ($ids as $value) {
                    $tmp = $this->calc($data, $value['id'], 'next');
                    if($tmp != NULL){
                        throw new Exception($tmp);
                    }
                }
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
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
