<?php

namespace App\Http\Controllers\API\Agent;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Order;
use App\Models\OrderAgent;
use App\Models\OrderTracking;
use App\Services\BillService;
use App\Repositories\BillRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AgentBillsController extends Controller
{
    protected $model, $modelDetail, $agentModel, $service, $repo;
    public function __construct(
        Bill $model, 
        BillDetail $modelDetail,
        Agent $agentModel,
        BillService $service,
        BillRepository $repo
        )
    {
        $this->model = $model;
        $this->agentModel = $agentModel;
        $this->service = $service;
        $this->repo = $repo;
        //$this->modelTracking = $modelTracking;
    }

    public function index(Request $request)
    {
        /*$datas = $this->model->where('last_status','<>','Open')
                            ->whereRelation('agent', 'agent_id', auth()->user()->agent_id)->get();
                            */
        $datas = $this->model->with('details')
                            ->where('agent_id', auth()->user()->agent_id)
                            ->where('last_status','<>','admin')->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function listOrderDelivered(Request $request)
    {
        $datas = DB::select("select 
        A.id, A.order_number, A.awb_no, B.customer_name, A.origin, C.city_name as destination, 
        A.total_kg_agent, A.delivered_date, D.service_name, A.last_status
        from orders A
        join customers B on A.customer_id=B.id
        join cities C on A.destination=C.id
        join services D on A.service_id=D.id
        where A.agent_id=".auth()->user()->agent_id." and A.last_status='Delivered'
        union all
        select A.id, A.order_number, A.awb_no, B.customer_name, A.origin, C.city_name as destination, 
        A.total_kg_agent, A.delivered_date, D.service_name, A.last_status
        from orders A
        join customers B on A.customer_id=B.id
        join cities C on A.destination=C.id
        join services D on A.service_id=D.id
        join order_agents E on A.order_number=E.order_number
        where E.agent_id=".auth()->user()->agent_id." and A.last_status='Delivered'");
        return ResponseFormatter::success($datas,'OK');
    }

    /*public function show($order_number)
    {
        $id = $this->agentModel->where('agent_id', auth()->user()->agent_id)->get();
        $datas = $this->model->with('customers','customer_branchs','origins','destinations','services','servicegroups','units','costs','references')
                    ->where('order_number',$id[0]->order_number)
                    ->first();
        return ResponseFormatter::success($datas,'OK');
    }

    public function track($order_number)
    {
        $datas = $this->modelTracking->where('order_number',$order_number)
                    ->where('is_admin_view',0)
                    ->orderBy('status_date','desc')
                    ->get();
        return ResponseFormatter::success($datas,'OK');
    }
    */

    public function detail($no)
    {
        $datas = $this->repo->showByAgentID($no,auth()->user()->agent_id);
        return ResponseFormatter::success($datas,'OK');
    }

    public function store(Request $request)
    {
        //return ResponseFormatter::success(auth()->user()->agent_id,'OK');
        return $this->service->create($request, $request->all(), auth()->user()->agent_id);
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($request, $request->all(), $id);
    }

    public function closing(Request $request, $id)
    {
        return $this->service->closing($request, $request->all(), $id);
    }

    public function delete($id)
    {
        return $this->service->destroy($id);
    }
}