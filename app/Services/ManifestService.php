<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;
use Exception, Log, Image;

use App\Repositories\ManifestRepository;
use App\Repositories\ManifestDetailRepository;
use App\Repositories\ManifestCogRepository;
use App\Repositories\AgentRepository;
use App\Repositories\BranchRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderAgentRepository;
use App\Repositories\OrderUnitRepository;
use App\Repositories\OrderTrackingRepository;
use App\Repositories\TripDetailRepository;

class ManifestService extends BaseService
{
    protected $repo, $repoOrderAgent, $repoAgent, $repoBranch;

    public function __construct(
        ManifestRepository $repo,
        ManifestDetailRepository $repoDetail,
        ManifestCogRepository $repoCogs,
        OrderRepository $repoOrder,
        OrderUnitRepository $repoOrderUnit,
        OrderTrackingRepository $repoTracking,
        OrderAgentRepository $repoOrderAgent,
        AgentRepository $repoAgent,
        BranchRepository $repoBranch,
        TripDetailRepository $repoTripDetail
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoDetail = $repoDetail;
        $this->repoOrder = $repoOrder;
        $this->repoCogs = $repoCogs;
        $this->repoOrderUnit = $repoOrderUnit;
        $this->repoTracking = $repoTracking;
        $this->repoOrderAgent = $repoOrderAgent;
        $this->repoAgent = $repoAgent;
        $this->repoBranch = $repoBranch;
        $this->repoTripDetail = $repoTripDetail;
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

    public function manifestByDriverID($driver_id)
    {
        $data = $this->repo->getManifestByDriverID($driver_id);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function manifestDriverDetail($manifest_number)
    {
        $data = $this->repo->getManifestDriverDetail($manifest_number);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function manifestDriverAgent($manifest_number)
    {
        $data = $this->repo->getManifestDriverAgent($manifest_number);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function list(Request $request)
    {
        $data = $this->repo->getList($request);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function sttList(Request $request, $manifest_number)
    {
        $data = $this->repo->getSttList($request, $manifest_number);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function manifestAgent(Request $request, $manifest_number)
    {
        $data = $this->repo->getManifestAgent($request, $manifest_number);
        if ($data != NULL) {
            return ResponseFormatter::success($data,'OK');
        } else {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }
    }

    public function schedule(Request $request)
    {
        $data = $this->repo->getSchedule($request);
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
                'manifest_name' => 'required|max:255|unique:manifests,manifest_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            $order_number = explode(',', $request->order_number);
            //validasi tidak boleh hanya 1 order/stt
            //disable for testing
            /*if(count($order_number) <=1){
                throw new Exception('Data Order STT yang akan dibuat manifest harus lebih dari 1');
            }*/

            //validasi harus satu kota yg sama
            /*$cek = $this->repoOrder->isSameDestination($order_number);
            if(!$cek){
                throw new Exception('Data Order STT tidak boleh berbeda kota tujuan !!');
            }else{*/

                //skip dulu
                /*if(count($order_number) >1){
                    //cek harus agent yg sama
                    $cek_agent = $this->repoOrderAgent->isSameAgent($order_number);
                    if(!$cek_agent){
                        throw new Exception('Data Order STT tidak boleh berbeda agent !!');
                    }
                }*/
                
                //cek harus service yg sama
                /*$cek_groupservice = $this->repoOrder->isSameGroupService($order_number);
                if(!$cek_groupservice){
                    throw new Exception('Data Order STT tidak boleh berbeda group service !!');
                }

                //cek harus service yg sama
                $cek_service = $this->repoOrder->isSameService($order_number);
                if(!$cek_service){
                    throw new Exception('Data Order STT tidak boleh berbeda service/layanan !!');
                }*/
            //}

            //get destination
            $destination = $this->repoOrder->getSameDestination($order_number);
            if($destination == NULL){
				throw new Exception($destination);
			}
            
            //get total colly
            $total_colly = $this->repoOrder->getSumColly($order_number);

            //get total kilogram
            $total_kg = $this->repoOrder->getSumKilogram($order_number);

            //insert table
            $data['total_colly']    = $total_colly;
            $data['total_kg']       = str_replace(',','',$total_kg);
            $data['total_order']    = count($order_number);
            //$data['origin']         = "";
            $data['destination']    = $destination;
            $data['last_status']    = "Open";
            $data['user_id'] = auth()->user()->id;
            $id = $this->repo->createWithID($data);

            //get manifest number
            $manifest_number = $this->repo->getManifestNumber($id);

            $data_detail = [];
            $data_order = [];
            $data_cogs = [];

            foreach ($order_number as $value) {
                
                //insert detail
                $data_detail['manifest_number'] = $manifest_number;
                $data_detail['order_number']    = $value;
                $data_detail['user_id']         = auth()->user()->id;
                $this->repoDetail->create($data_detail);

                //update all orders
                $data_order['last_status']     = "Warehouse";
                $data_order['user_id']         = auth()->user()->id;
                $this->repoOrder->updateByOrderNumber($data_order, $value);
            }
            
            DB::commit();
            
            return ResponseFormatter::success($manifest_number,'OK');
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
                'manifest_name' => ['required','max:255',Rule::unique('manifests', 'manifest_name')->ignore($id)]
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            //check driver and truck
            /*$check = $this->repo->isDriverTruckMatch($request->driver_id, $request->truck_id);
            if(!$check){
                throw new Exception('Driver dan Truck harus sama dengan manifest yang sedang berjalan !!');
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

    public function destroy($manifest_number)
    {
        DB::beginTransaction();
        try {

            if($this->repo->getLastStatusByManifestNumber($manifest_number) == "Closing"){
                throw new Exception("Manifest ini tidak bisa dihapus karena sudah diclosing");
            }else if($this->repo->getLastStatusByManifestNumber($manifest_number) == "Trip"){
                throw new Exception("Manifest ini tidak bisa dihapus karena sudah dibuatkan HPP nya");
            }

            $order_number = [];
            $order_number = $this->repoDetail->getListOrderByManifestNumber($manifest_number);
            $data_order = [];
            $data_order['last_status']  = 'Closing';
            $data_order['user_id']      = auth()->user()->id;
            foreach ($order_number as $value) {
                //ubah status orders
                $this->repoOrder->updateByOrderNumber($data_order, $value->order_number);
            }

            $this->repoDetail->deleteByManifestNumber($manifest_number);
            $this->repo->deleteByManifestNumber($manifest_number);

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
            if($this->repo->isAllowClosing($request->manifest_number) == false){
                throw new Exception("Manifest ini tidak bisa diclosing karena data belum lengkap");
            }

            $data['last_status']        = 'Closing';
            $data['last_tracking']      = 'On Process Delivery';
            $data['user_id']            = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);

            //$tmp = $this->repo->show($request->id);
            //$manifest_number = $tmp[0]->manifest_number;
            $order_number = [];
            $order_number = $this->repoDetail->getListOrderByManifestNumber($request->manifest_number);

            $data_order = [];
            $data_tracking = [];
            foreach ($order_number as $value) {
                //update all orders
                $data_order['last_status']     = "Manifested";
                $data_order['user_id']         = auth()->user()->id;
                $this->repoOrder->updateByOrderNumber($data_order, $value->order_number);

                //insert tracking orders
                /*$data_tracking['status_date']     = Carbon::now()->toDateTimeString();
                $data_tracking['status_name']     = 'Manifested';
                $data_tracking['order_number']    = $value->order_number;
                $data_tracking['user_id']         = auth()->user()->id;
                $this->repoTracking->create($data_tracking);
                */
            }

            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function manifestUpdateTracking(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            Log::debug($request->all());
            //throw new exception($request->manifest_number);
            $city_id = 0;
            $is_branch = 0;

            //STEP 1 - INSERT FIRST MANIFEST NUMBER
            
            //looping update orders
            $order_number = $this->repoDetail->getListOrderByManifestNumber($request->manifest_number);
            //Log::debug($order_number);
            if(empty($order_number)){
                throw new exception('Data STT di manifest ini sudah ter-delivered semua. Harap hubungi Administrator');
            }
            
            foreach ($order_number as $value) {
                
                if (!empty($request->filename)) {
                    $timestamp = time();
                    $fileName = $timestamp. "_" . $value->order_number . ".jpg";
                    //blob to base64 image
                    $base64_image = $request->filename;
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                        $datas = substr($base64_image, strpos($base64_image, ',') + 1);
                
                        $datas = base64_decode($datas);
                        /*$resized = Image::make($datas);
                        $resized->resize(1280, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $resized->save(storage_path().'/app/orders/file/'.$fileName);
                        */
                        Storage::put('orders/file/'.$fileName, $datas);
                    }

                    $data['filename']       = $fileName;
                }

                $data['order_number']   = $value->order_number;
                $data['status_date']    = Carbon::now()->toDateTimeString();
                $data['user_id']        = auth()->user()->id;

                if($request->status == "Transit"){
                    if($request->transit_city_id == ""){
                        $tmp = $request->agent;
                        $tmpArray = explode('-', $tmp);
                        //throw new exception($tmpArray[0]);
                        $agent_id = $this->repoOrderAgent->getAgentID($tmpArray[0]);
                        //throw new exception($agent_id);
                        if($agent_id == NULL){
                            $is_branch = 1;
                            $agent_id = $this->repoOrderAgent->getBranchID($tmpArray[0]);
                            $city_id = $this->repoBranch->getBranchCityID($agent_id);
                        }else{
                            //check if origin city data is avaiable
                            $tmp_origin = $this->repoOrderAgent->getAgentOriginID($tmpArray[0]);
                            //throw new exception($tmp_origin);
                            if($tmp_origin != NULL){
                                $city_id = $tmp_origin;
                            }else{
                                $city_id = $this->repoAgent->getAgentCityID($agent_id);
                            }
                        }
                    }else{
                        $city_id = $request->transit_city_id;
                    }

                    $data['is_admin_view']  = 0;
                    $data['city_id']        = $city_id;

                    if($request->agent != "-"){
                        $data['status_name']    = $request->status." [".$request->agent."]";
                    }else{
                        $data['status_name']    = $request->status;
                    }

                    $data_order = [];
                    $data_order['last_status']   = $request->status;
                }

                if($request->status == "Delivered"){
                    $data['is_admin_view']   = 0;
                    $data['city_id']         = $this->repoOrder->getDestination($value->order_number);
                    $data['status_name']     = $request->status;
                    $data['recipient']       = $request->recipient;

                    
                    $data_order = [];
                    $data_order['last_status']   = $request->status;
                    $data_order['delivered_date']   = Carbon::now()->toDateTimeString();
                }

                //update order
                $this->repoOrder->updateByOrderNumber($data_order, $value->order_number);

                //create data tracking
                $this->repoTracking->create($data);
            }
            
            //update manifest
            $data_mft = [];
            $data_mft['last_tracking']      = $request->status;

            //if transit agent or delivered then manifest can't be updated again
            if($request->transit_city_id != "" && $request->status == "Transit" ){
                $data_mft['is_already_track']   = "N";
            }else{
                $data_mft['is_already_track']   = "Y";
            }
            
            $result = $this->repo->updateByManifestNumber($data_mft, $request->manifest_number);

            //STEP 2 - UPDATE OTHER MANIFEST
            //get list manifest in same trip
            /*$manifest_number = $this->repoTripDetail->getListOtherManifestNumber($request->manifest_number);

            foreach ($manifest_number as $value) {

                $isAlreadyTrack = $this->repo->isAlreadyTrack($value['manifest_number']);
                //throw new exception($isAlreadyTrack);
                //if status not already track, then update
                if($isAlreadyTrack == "N"){
                    //looping update orders
                    $order_number = $this->repoDetail->getListOrderByManifestNumber($value['manifest_number']);
                    foreach ($order_number as $ord) {

                        $data = [];
                        $data['order_number']   = $ord->order_number;
                        $data['status_name']    = 'Transit';
                        $data['status_date']    = Carbon::now()->toDateTimeString();
                        $data['user_id']        = auth()->user()->id;
                        $data['is_admin_view']  = 0;
                        $data['city_id']        = $city_id;
                            
                        $data_order = [];
                        $data_order['last_status']   = $request->status;
        
                        //update order
                        $this->repoOrder->updateByOrderNumber($data_order, $ord->order_number);
        
                        //create data tracking
                        $this->repoTracking->create($data);
                    }

                    //update manifest
                    $data_mft = [];
                    $data_mft['last_tracking']      = $request->status;
                    $result = $this->repo->updateByManifestNumber($data_mft, $value['manifest_number']);
                }
            }*/


            DB::commit();
            
            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            Log::debug('Update Manifest Tracking : '.$exc);
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
