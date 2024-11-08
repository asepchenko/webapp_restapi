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
use File, Exception, Log, Image;

use App\Repositories\AgentRepository;
use App\Repositories\BranchRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderAgentRepository;
use App\Repositories\OrderTrackingRepository;

class OrderTrackingService extends BaseService
{
    protected $repo, $repoOrder, $repoOrderAgent, $repoAgent, $repoBranch;

    public function __construct(
        OrderTrackingRepository $repo,
        OrderRepository $repoOrder,
        OrderAgentRepository $repoOrderAgent,
        AgentRepository $repoAgent,
        BranchRepository $repoBranch
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoOrder = $repoOrder;
        $this->repoOrderAgent = $repoOrderAgent;
        $this->repoAgent = $repoAgent;
        $this->repoBranch = $repoBranch;
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
            Log::debug($request->except(['filename']));
            //Log::debug($request->all());
            $today = $request->tracking_date.' '.$request->tracking_time;
            /*$validator = Validator::make($data, [
                'filename' => 'file|mimes:jpg,jpeg,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/
            //throw new Exception($request->filename);
            

            //file upload processing
            //if(isset($request->filename)){
                //$ext = $request->filename->getClientOriginalExtension();
                //$ori_file = $request->filename->getClientOriginalName();
                //$ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                //$fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                //$request->filename->storeAs('orders/file', $fileName);
            if (!empty($request->filename)) {
                $timestamp = time();
                $fileName = $timestamp. "_" . $request->order_number . ".jpg";
                //blob to base64 image
                $base64_image = $request->filename;

                //CAUTION
                //set memory_limit on server/hosting to -1 to avoid this error
                //file_put_contents(storage_path().'/orders/file/'.$fileName, file_get_contents($image));
                if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                    $datas = substr($base64_image, strpos($base64_image, ',') + 1);
                
                    $datas = base64_decode($datas);
                    //$resized = Image::make($datas)->resize(1280, 720); //HD 720p

                    //ini_set('memory_limit', '512M');
                    /*$resized = Image::make($datas);
                    $resized->resize(1280, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $resized->save(storage_path().'/app/orders/file/'.$fileName);*/
                    Storage::put('orders/file/'.$fileName, $datas);
                    Log::debug(storage_path().'/app/orders/file/'.$fileName);
                }

                $data['filename'] = $fileName;
            }
            //}

            $data['status_date']     = $today; //Carbon::now()->toDateTimeString();

            if($request->status == "Transit"){

                if($request->transit_city_id == ""){
                    $tmp = $request->agent;
                    $tmpArray = explode('-', $tmp);
                    //throw new exception($tmpArray[0]);
                    $agent_id = $this->repoOrderAgent->getAgentID($tmpArray[0]);
                    if($agent_id == NULL){
                        $branch_id = $this->repoOrderAgent->getBranchID($tmpArray[0]);
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
                    $city_id= $request->transit_city_id;
                }

                $data['is_admin_view']  = 0;
                $data['city_id']        = $city_id;
                if($request->agent != "-"){
                    $data['status_name']    = $request->status." [".$request->agent."]";
                }else{
                    $data['status_name']    = $request->status;
                }
            }
            /*$data['status_name']     = $request->status;
            $data['recipient']       = $request->recipient;
            $data['description']     = $request->description;
            $data['order_number']    = $request->order_number;*/

            if($request->status == "Delivered"){
                $data['is_admin_view']   = 0;
                $data['city_id']         = $this->repoOrder->getDestination($request->order_number);
                $data['status_name']     = $request->status;

                //update order
                $data_order = [];
                $data_order['last_status']      = $request->status;
                $data_order['delivered_date']   = $today; //Carbon::now()->toDateTimeString();
                $this->repoOrder->updateByOrderNumber($data_order, $request->order_number);
            }else{
                //update order
                $data_order = [];
                $data_order['last_status']      = $request->status;
                $this->repoOrder->updateByOrderNumber($data_order, $request->order_number);
            }

            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->create($data);

            DB::commit();
            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            Log::debug('Update Tracking Order - '.$request->order_number.' : '.$exc);
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, array $data, $id)
    {
        DB::beginTransaction();
        try {
            //validating
            /*$validator = Validator::make($data, [
                'ordertracking_name' => ['required','max:255',Rule::unique('ordertrackings', 'ordertracking_name')->ignore($id)]
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
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
