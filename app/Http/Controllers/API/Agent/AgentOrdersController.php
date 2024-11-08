<?php

namespace App\Http\Controllers\API\Agent;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAgent;
use App\Models\OrderAgentDestination;
use App\Models\OrderTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Repositories\AgentRepository;
use App\Repositories\BranchRepository;
use App\Repositories\OrderAgentRepository;

class AgentOrdersController extends Controller
{
    protected $model, $modelTracking, $agentModel, $repoAgent, $repoBranch, $repoOrderAgent, $modelOrderAgentDest;
    public function __construct(
        Order $model, 
        OrderAgent $agentModel,
        OrderTracking $modelTracking,
        OrderAgentDestination $modelOrderAgentDest,
        AgentRepository $repoAgent,
        BranchRepository $repoBranch,
        OrderAgentRepository $repoOrderAgent
    )
    {
        $this->model = $model;
        $this->agentModel = $agentModel;
        $this->modelTracking = $modelTracking;
        $this->modelOrderAgentDest = $modelOrderAgentDest;
        $this->repoAgent = $repoAgent;
        $this->repoBranch = $repoBranch;
        $this->repoOrderAgent = $repoOrderAgent;
    }

    public function index(Request $request)
    {
        /*$datas = $this->model->where('last_status','<>','Open')
                            ->whereRelation('agent', 'agent_id', auth()->user()->agent_id)->get();
                            */
        $id = $this->agentModel->where('agent_id', auth()->user()->agent_id)->get('order_number');
        /*$id = DB::select("select A.order_number 
        from order_agents A
        join agents B on A.agent_id=B.id
        join order_trackings C on B.city_id=C.city_id 
        where agent_id =".auth()->user()->agent_id."");*/

        $datas = $this->model->with('origins','destinations','units','trackings')
                            ->whereIn('order_number', $id)->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getIndexByDate($start_date, $end_date)
    {
        $where = " and A.pickup_date >='".$start_date."'";
        $where .= " and A.pickup_date <='".$end_date."'";

        $datas = DB::select("
        select AA.awb_no, AA.order_number, AA.total_colly, AA.total_kg_agent,
        AA.origin, AA.destination, AA.delivered_date, AA.pickup_date, AA.service_name,AA.recipient,
        case when AA.delivered_date is not null Then 'Delivered' 
                when AA.delivered_date is null Then 'On Process Delivery' end 
                as last_status
        from (select A.awb_no, A.order_number,A.total_colly, A.total_kg_agent,
        -- B.customer_name, C.branch_name,
        J.city_name as origin, 
        ifnull((select Y.city_name
        from order_agents X
        join agents Z on X.agent_id=Z.id
        join cities Y on Z.city_id=Y.id
        where X.order_number=A.order_number and X.sequence=G.sequence+1),J.city_name) as destination,
        -- (select YY.delivered_date 
        -- from order_agent_destinations YY
        -- where YY.order_number=A.order_number and YY.agent_id_origin = G.agent_id) as delivered_date,
		A.delivered_date,
        A.pickup_date, D.service_name,
        '' as recipient
        from orders A
        -- join customers B on A.customer_id=B.id
        -- join customer_branchs C on A.customer_branch_id=C.id
        join services D on A.service_id=D.id
        join cities F on A.destination=F.id
        join order_agents G on A.order_number=G.order_number
        join agents H on G.agent_id=H.id
        join cities J on H.city_id=J.id
        where G.agent_id=".auth()->user()->agent_id." ".$where."
        ) as AA
        order by AA.pickup_date desc");
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($order_number)
    {
        $id = $this->agentModel->where('order_number', $order_number)
                                ->where('agent_id', auth()->user()->agent_id)
                                ->get();
        if ($id->count() == 0) {
            return ResponseFormatter::error($data,'Data Not Found','404');
        }

        $datas = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','units','costs','references','agent')
                    ->where('order_number',$id[0]->order_number)
                    ->first();
        return ResponseFormatter::success($datas,'OK');
    }

    public function getOrderLimit5()
    {
        $id = $this->agentModel->where('agent_id', auth()->user()->agent_id)->get('order_number');
        $datas = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','units','costs','references')
                    ->whereIn('order_number', $id)
                    ->orderBy('updated_at','desc')->limit(5)->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function listAgent($order_number)
    {
        $tmp = DB::select("select (sequence+1) as seq from order_agents
        where order_number = '".$order_number."' and agent_id=".auth()->user()->agent_id."");
        $seq = $tmp[0]->seq;

        $datas = DB::select("select z.* from(
            select A.id as id, B.agent_code as agent_code, B.agent_name as agent_name, C.area_name as area, A.sequence as sequence
            from order_agents A
            join agents B on A.agent_id =B.id
            join areas C on B.area_id = C.id
            where A.order_number = '".$order_number."' and A.branch_id is null and A.sequence=".$seq."
            union all
            select A.id as id, B.branch_code as agent_code, B.branch_name as agent_name, C.city_name as area, A.sequence as sequence
            from order_agents A
            join branchs B on A.branch_id =B.id
            join cities C on B.city_id=C.id
            where A.order_number = '".$order_number."' and A.agent_id is null and A.sequence=".$seq."
            )as z
            order by z.sequence");
        return ResponseFormatter::success($datas,'OK');
    }

    public function isDelivered($order_number)
    {
        //$datas = DB::select("select delivered_date from order_agent_destinations
        //where order_number='".$order_number."' and agent_id_origin=".auth()->user()->agent_id."");

        $datas = DB::select("select count(delivered_date) as deliv from orders
        where order_number='".$order_number."'");
		
        if($datas[0]->deliv > 0){
            $res = "true";
        }else{
            $res = "false";
        }
        return ResponseFormatter::success($res,'OK');
    }

    public function track($order_number)
    {
        $datas = $this->modelTracking->with('cities')->where('order_number',$order_number)
                    ->where('is_admin_view',0)
                    ->orderBy('status_date','desc')
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [];
            /*$validator = Validator::make($data, [
                'filename' => 'file|mimes:jpg,jpeg,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            $timestamp = time();

            //file upload processing
            //if(isset($request->filename)){
                //$ext = $request->filename->getClientOriginalExtension();
                //$ori_file = $request->filename->getClientOriginalName();
                //$ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                //$fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                //$request->filename->storeAs('orders/file', $fileName);

                //$fileName = $timestamp. "_" . $$request->order_number . ".jpg";
                //file_put_contents(storage_path().'/orders/file/'.$fileName, file_get_contents($request->filename));
                
                $fileName = $timestamp. "_" . $request->order_number . ".jpg";
                $base64_image = $request->filename;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                    $datas = substr($base64_image, strpos($base64_image, ',') + 1);
                    $datas = base64_decode($datas);
                    Storage::put('orders/file/'.$fileName, $datas);
                }

                $data['filename'] = $fileName;
            //}

            $data['status_date']     = Carbon::now()->toDateTimeString();
            $data['recipient']       = $request->recipient;
            $data['description']     = $request->description;
            $data['order_number']    = $request->order_number;

            //origin
            $origin_city_id = $this->repoAgent->getAgentCityID(auth()->user()->agent_id);

            if($request->status == "Transit"){
                $tmp = $request->agent;
                $tmpArray = explode('-', $tmp);
                
                //destination
                $agent_id = $this->repoOrderAgent->getAgentByID($tmpArray[0]);
                if($agent_id == NULL){
                    //get by branch
                    $agent_id = $this->repoOrderAgent->getBranchByID($tmpArray[0]);
                    $city_id = $this->repoBranch->getBranchCityID($agent_id);
                }else{
                    $city_id = $this->repoAgent->getAgentCityID($agent_id);
                }

                $data['is_admin_view']   = 0;
                $data['city_id']         = $city_id;
                $data['status_name']     = $request->status." [".$request->agent."]";

                $data_agent = [];
                $data_agent['order_number']         = $request->order_number;
                $data_agent['agent_id_origin']      = auth()->user()->agent_id;
                $data_agent['origin']               = $origin_city_id;
                $data_agent['agent_id_destination'] = $agent_id;
                $data_agent['destination']          = $city_id;
                $data_agent['delivered_date']       = Carbon::now()->toDateTimeString();
                $data_agent['user_id']              = auth()->user()->agent_id;
                $this->modelOrderAgentDest->create($data_agent);
            }

            if($request->status == "Delivered"){
                $ord = $this->model->where('order_number',$request->order_number)->first();
                $data['is_admin_view']  = 0;
                $data['status_name']    = $request->status;
                $data['city_id']        = $ord->destination;

                $data_agent = [];
                $data_agent['order_number']         = $request->order_number;
                $data_agent['agent_id_origin']      = auth()->user()->agent_id;
                $data_agent['origin']               = $origin_city_id;
                $data_agent['destination']          = $ord->destination;
                $data_agent['delivered_date']       = Carbon::now()->toDateTimeString();
                $data_agent['user_id']              = auth()->user()->agent_id;
                $this->modelOrderAgentDest->create($data_agent);
            }
            
            $data['user_id']         = auth()->user()->agent_id;

            $this->modelTracking->create($data);

            //update order
            $data_order['last_status']   = $request->status;
            if($request->status == "Delivered"){
                $data_order['delivered_date']   = Carbon::now()->toDateTimeString();
            }
            $object = $this->model->where('order_number', $request->order_number)->firstOrFail();
            $object->fill($data_order);
            $object->save();

            DB::commit();
            return ResponseFormatter::success($request->order_number,'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}