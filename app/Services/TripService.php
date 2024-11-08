<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Services\ManifestCogService;
use App\Services\TripCityService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\TripRepository;
use App\Repositories\TripDetailRepository;
use App\Repositories\TripCityRepository;
use App\Repositories\ManifestRepository;
use App\Repositories\ManifestDetailRepository;
use App\Repositories\ManifestCogRepository;
use App\Repositories\OrderRepository;

use Yudhatp\Helpers\Helpers;

class TripService extends BaseService
{
    protected $repo;

    public function __construct(
        TripRepository $repo,
        TripDetailRepository $repoDetail,
        TripCityRepository $repoCity,
        OrderRepository $repoOrder,
        ManifestRepository $repoManifest,
        ManifestDetailRepository $repoManifestDetail,
        ManifestCogRepository $repoManifestCogs,
        ManifestCogService $mftCogsService,
        TripCityService $tripCityService
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoDetail = $repoDetail;
        $this->repoCity = $repoCity;
        $this->repoManifest = $repoManifest;
        $this->repoManifestDetail = $repoManifestDetail;
        $this->repoManifestCogs = $repoManifestCogs;
        $this->mftCogsService = $mftCogsService;
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

    public function show($id)
    {
        $data = $this->repo->show($id);
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

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            //validating
            /*$validator = Validator::make($data, [
                'trip_name' => 'required|max:255|unique:trips,trip_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            
            $manifest_number = explode(',', $request->manifest_number);
            //validasi tidak boleh hanya 1 order/stt
            /*if(count($manifest_number) <=1){
                throw new Exception('Data Order STT yang akan dibuat manifest harus lebih dari 1');
            }

            //validasi harus satu kota yg sama
            $cek = $this->repoOrder->isSameDestination($manifest_number);
            if(!$cek){
                throw new Exception('Data Order STT tidak boleh berbeda kota tujuan !!');
            }else{
                //cek harus agent yg sama
                $cek_agent = $this->repoOrderAgent->isSameAgent($manifest_number);
                if(!$cek_agent){
                    throw new Exception('Data Order STT tidak boleh berbeda agent !!');
                }
            }*/

            //cek harus service group yg sama
            $arr_order_number = $this->repoManifestDetail->getArrayListOrderByManifestNumber($manifest_number);
            $cek_groupservice = $this->repoOrder->isSameGroupService($arr_order_number);
            if(!$cek_groupservice){
                throw new Exception('Data Order STT didalam manifest tidak boleh berbeda group service !!');
            }

            //cek apakah ini tipe trucking
            $is_trucking = $this->repoOrder->isTrucking($arr_order_number);

            //cleansing format
            $ops_cost         = str_replace(',','',$request->operational_cost);
            $mlt_num          = str_replace(',','',$request->multiplier_number);

            //insert table
            $data['operational_cost']       = $ops_cost;
            $data['multiplier_number']      = $mlt_num;
            $data['last_status']            = "Open";
            $data['user_id']                = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get data trip
            $trip = $this->repo->getDataTripByID($id);

            $data_detail    = [];
            $data_cogs      = [];
            $data_mft       = [];

            $multiplier_number = 1;
            $sum_kg = 0;

            //for detail manifest
            foreach ($manifest_number as $value) {
                
                //insert detail
                $data_detail['trip_number']         = $trip[0]->trip_number;
                $data_detail['manifest_number']     = $value;
                $data_detail['user_id']             = auth()->user()->id;
                $this->repoDetail->create($data_detail);

                //get destination
                $tmp_mft = $this->repoManifest->getManifestDestKilo($value);

                if($is_trucking){
                    $mft_kg = 1;
                }else{
                    $mft_kg = Helpers::calcIDFormatDecimal($tmp_mft[0]->total_kg);
                }
                
                //insert manifest cogs/hpp
                $data_cogs['trip_number']         = $trip[0]->trip_number;
                $data_cogs['manifest_number']     = $value;
                $data_cogs['city_id']             = $tmp_mft[0]->destination;
                $data_cogs['kg']                  = $mft_kg;
                $sum_kg = ($sum_kg + $mft_kg);

                //calc by formula
                $data_cogs['multiplier']            = $mlt_num;
                $data_cogs['multiplier_number']     = $multiplier_number;
                $data_cogs['cogs_kg']               = $this->floor($mlt_num * $multiplier_number);

                $data_cogs['user_id']               = auth()->user()->id;
                $this->repoManifestCogs->create($data_cogs);

                //update manifest
                $data_mft['last_status']     = "Trip";
                $data_mft['user_id']         = auth()->user()->id;
                $this->repoManifest->updateByManifestNumber($data_mft, $value);
                $multiplier_number = $multiplier_number + 0.1;
            }

            //calc & update cogs manifest
            $data_cogs      = [];
            //$sum_kg = $this->repoManifestCogs->getSumKilogram($manifest_number);

            foreach ($manifest_number as $value) {
                //total all kg trip / operational cost
                $avg_ops_cost = $ops_cost / $sum_kg;
                $data_cogs['avg_ops_cost']      = $this->floor($avg_ops_cost);

                //total all cogs_kg / count city or manifest
                $sum_cogs_kg = $this->repoManifestCogs->getSumCogsKg($manifest_number);
                $cogs_avg = Helpers::calcIDFormatDecimal($sum_cogs_kg) / count($manifest_number);
                $data_cogs['cogs_avg']          = $cogs_avg;

                $tmp_cogs = $this->repoManifestCogs->getDataByManifestNumber($value);
                $cogs_kg = Helpers::calcIDFormatDecimal($tmp_cogs[0]->cogs_kg);
                $kg = Helpers::calcIDFormatDecimal($tmp_cogs[0]->kg);
                $tmp = $cogs_kg - Helpers::calcIDFormatDecimal($cogs_avg);
                $data_cogs['diff_cogs_avg_ops']  = $tmp;

                $cogs_real_kg = $tmp + $avg_ops_cost;
                $data_cogs['cogs_real_kg']       = $cogs_real_kg;

                $data_cogs['cogs_real_city']     = $this->floor($cogs_real_kg * $kg);
                $data_cogs['user_id']            = auth()->user()->id;
                $this->repoManifestCogs->updateByManifestNumber($data_cogs, $value);
            }
            

            $data_cities = [];
            $multiplier_number = 1;
            $sum_kg = 0;
            //$arr_city = $this->repoOrder->getArrayListCityByOrderNumber($arr_order_number);
            $arr_city = $this->repoOrder->sumDataByOrderNumberCityID($arr_order_number);
            //insert to trip city (sum from manifest cogs group by city)
            foreach ($arr_city as $city) {
                //$sum = $this->repoOrder->sumDataByOrderNumberCityID($arr_order_number, $arr_city);
                $data_cities['trip_number']         = $trip[0]->trip_number;
                $data_cities['city_id']             = $city->city_id;
                $data_cities['multiplier']          = $mlt_num;
                $data_cities['multiplier_number']   = $multiplier_number;
                $data_cities['cogs_kg']             = $this->floor($mlt_num * $multiplier_number);

                if($is_trucking){
                    $mft_kg = 1;
                }else{
                    $mft_kg = $city->kg;
                }

                $data_cities['kg']                  = $mft_kg;
                $sum_kg = ($sum_kg + $mft_kg);
                $data_cities['user_id']             = auth()->user()->id;
                $this->repoCity->create($data_cities);
                $multiplier_number = $multiplier_number + 0.1;
            }

            //update trip cities/calc hpp
            $data_city_cogs = [];
            foreach ($arr_city as $city) {
                //total all kg trip / operational cost
                $avg_ops_cost = $ops_cost / $sum_kg;
                $data_city_cogs['avg_ops_cost']      = $this->floor($avg_ops_cost);

                //total all cogs_kg / count city or manifest
                $sum_cogs_kg = $this->repoCity->getSumCogsKg($trip[0]->trip_number);
                $cogs_avg = Helpers::calcIDFormatDecimal($sum_cogs_kg) / count($arr_city);
                //$cogs_avg = $dt_city[0]->cogs_kg / count($arr_city);
                $data_city_cogs['cogs_avg']          = $cogs_avg;

                $dt_city = $this->repoCity->getDataByTripNumberAndCityID($trip[0]->trip_number, $city->city_id);
                $cogs_kg = Helpers::calcIDFormatDecimal($dt_city[0]->cogs_kg);
                $kg = Helpers::calcIDFormatDecimal($dt_city[0]->kg);
                $tmp = $cogs_kg - Helpers::calcIDFormatDecimal($cogs_avg);
                $data_city_cogs['diff_cogs_avg_ops']  = $tmp;

                $cogs_real_kg = $tmp + $avg_ops_cost;
                $data_city_cogs['cogs_real_kg']       = $cogs_real_kg;
                $data_city_cogs['cogs_real_city']     = $this->floor($cogs_real_kg * $kg);
                $this->repoCity->updateByTripNumberAndCityID($data_city_cogs, $trip[0]->trip_number, $city->city_id);
            }
            DB::commit();

            return ResponseFormatter::success($trip[0]->trip_number,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {

            $data_cogs      = [];
            //cleansing format
            $ops_cost         = str_replace(',','',$request->operational_cost);
            $mlt_num          = str_replace(',','',$request->multiplier_number);

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->update($data, $id);

            $manifest_number = $this->repoDetail->getListManifestNumber($request->trip_number);
            //return ResponseFormatter::error($manifest_number, 'test','400');

            //hitung ulang cogs/hpp part 1
            /*foreach ($manifest_number as $value) {
                $tmp = $this->repoManifestCogs->getDataByManifestNumber($value['manifest_number']);
                $data_cogs['multiplier']            = $mlt_num;
                $data_cogs['multiplier_number']     = $tmp[0]->multiplier_number;
                $data_cogs['cogs_kg']               = $this->floor($mlt_num * $tmp[0]->multiplier_number);
    
                $data_cogs['user_id']               = auth()->user()->id;
                $this->repoManifestCogs->updateByManifestNumber($data_cogs, $value['manifest_number']);
                
                //calc hpp
                $param = [];
                $param['multiplier_number'] = $tmp[0]->multiplier_number;
                $param['trip_id']           = $id;
                $this->mftCogsService->calc($param, $tmp[0]->id, 'first');
            }

            //hitung ulang cogs/hpp part 2
            foreach ($manifest_number as $value) {
                $tmp = $this->repoManifestCogs->getDataByManifestNumber($value['manifest_number']);
                //calc hpp
                $param = [];
                $param['multiplier_number'] = $tmp[0]->multiplier_number;
                $param['trip_id']           = $id;
                $this->mftCogsService->calc($param, $tmp[0]->id, 'first');
            }*/



            //update trip cities/calc hpp
            
            //$arr_order_number = [];
            //$arr_order_number = $this->repoCity->getOrderNumberByTripNumber($request->trip_number);
            $arr_order_number = $this->repoManifestDetail->getArrayListOrderByManifestNumber($manifest_number);
            //throw new Exception(print_r($arr_order_number));
            //return ResponseFormatter::error($arr_order_number, 'test','400');
            $arr_city = $this->repoOrder->sumDataByOrderNumberCityID($arr_order_number);
            //return ResponseFormatter::error($arr_city, 'test','400');

            //hitung ulang cogs/hpp part 1
            foreach ($arr_city as $city) {
                $data_city_cogs = [];
                $tmp = $this->repoCity->getDataByTripNumberAndCityID($request->trip_number, $city->city_id);
                $data_city_cogs['multiplier']            = $mlt_num;
                $data_city_cogs['multiplier_number']     = $tmp[0]->multiplier_number;
                $data_city_cogs['cogs_kg']               = $this->floor($mlt_num * $tmp[0]->multiplier_number);
    
                $data_city_cogs['user_id']               = auth()->user()->id;
                $this->repoCity->updateByTripNumberAndCityID($data_city_cogs, $request->trip_number, $city->city_id);

                //calc hpp
                $param = [];
                $param['multiplier_number'] = $tmp[0]->multiplier_number;
                $param['trip_id']           = $id;
                $res = $this->tripCityService->calc($param, $tmp[0]->id, 'first');
                if($res != "OK"){
                    throw new exception($res);
                }
            }

            //hitung ulang cogs/hpp part 2
            foreach ($arr_city as $city) {
                $tmp = $this->repoCity->getDataByTripNumberAndCityID($request->trip_number, $city->city_id);
                //$tmp = $this->repoManifestCogs->getDataByManifestNumber($value['manifest_number']);
                //calc hpp
                $param = [];
                $param['multiplier_number'] = $tmp[0]->multiplier_number;
                $param['trip_id']           = $id;
                $res = $this->tripCityService->calc($param, $tmp[0]->id, 'next');
                if($res != "OK"){
                    throw new exception($res);
                }
            }
            /*$data_cogs      = [];
            foreach ($manifest_number as $value) {
                //total all kg trip / operational cost
                $sum_kg = $this->repoManifestCogs->getSumKilogram($request->trip_number);
                $avg_ops_cost = $request->operational_cost / str_replace(',','',$sum_kg);
                $data_cogs['avg_ops_cost']      = $avg_ops_cost;

                //total all cogs_kg / count city or manifest
                $sum_cogs_kg = $this->repoManifestCogs->getSumCogsKg($manifest_number);
                $cogs_avg = str_replace(',','',$sum_cogs_kg) / count($manifest_number);
                $data_cogs['cogs_avg']          = $cogs_avg;

                $tmp_cogs = $this->repoManifestCogs->getDataByManifestNumber($value);
                $tmp = str_replace(',','',$tmp_cogs[0]->cogs_kg) - $cogs_avg;
                
                $cogs_real_kg = $tmp + $avg_ops_cost;
                $data_cogs['cogs_real_kg']       = $cogs_real_kg;

                $data_cogs['cogs_real_city']     = $this->floor($cogs_real_kg * str_replace(',','',$tmp_cogs[0]->kg));
                $data_cogs['user_id']            = auth()->user()->id;
                $this->repoManifestCogs->updateByManifestNumber($data_cogs, $value);
            }*/

            
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function destroy($trip_number)
    {
        DB::beginTransaction();
        try {
            if($this->repo->getLastStatusByTripNumber($trip_number) == "Closing"){
                throw new Exception("Trip ini tidak bisa dihapus karena sudah closing");
            }

            if($this->repo->getLastTrackingStatusByTripNumber($trip_number) == "Process"){
                throw new Exception("Trip ini tidak bisa dihapus karena sudah dalam proses");
            }

            $manifest_number = $this->repoDetail->getListManifestNumber($trip_number);
            $data_mft = [];
            $data_mft['last_status']  = 'Closing';
            $data_mft['user_id']      = auth()->user()->id;
            foreach ($manifest_number as $value) {
                //ubah status orders
                $this->repoManifest->updateByManifestNumber($data_mft, $value);
            }

            $this->repoManifestCogs->deleteByTripNumber($trip_number);
            $this->repoDetail->deleteByTripNumber($trip_number);
            $this->repoCity->deleteByTripNumber($trip_number);
            $this->repo->deleteByTripNumber($trip_number);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function closing(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            //TODO : update order cogs based manifests
            

            //update orders status -> Sales
            $this->repo->updateOrdersByTripID($request->id, auth()->user()->id);
            
            $data['last_status']        = 'Closing';
            $data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
