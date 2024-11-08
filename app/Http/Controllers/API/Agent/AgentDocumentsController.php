<?php

namespace App\Http\Controllers\API\Agent;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentDetail;
use App\Models\Order;
use App\Models\OrderAgent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail; //send e-mail
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Exception;

class AgentDocumentsController extends Controller
{
    protected $model, $detailModel, $orderModel, $agentModel;
    public function __construct(
        Document $model, 
        DocumentDetail $detailModel,
        Order $orderModel,
        OrderAgent $agentModel
        )
    {
        $this->model = $model;
        $this->agentModel = $agentModel;
        $this->detailModel = $detailModel;
        $this->orderModel = $orderModel;
    }

    public function index(Request $request)
    {
        $datas = $this->model->with('users','details')
                            ->where('agent_id', auth()->user()->agent_id)
                            ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function indexList(Request $request)
    {
        $id = $this->agentModel->where('agent_id', auth()->user()->agent_id)->get('order_number');
        $datas = $this->orderModel->with('customers','units')
                            ->where('last_status','Delivered')
                            ->whereIn('order_number', $id)->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function showDetail(Request $request, $id)
    {
        $datas = $this->detailModel->with('order')
                            ->where('document_id', $id)
                            ->get();
        return ResponseFormatter::success($datas,'OK');
    }

    public function show($id)
    {
        $datas = $this->model->with('users','details')
                    ->where('agent_id', auth()->user()->agent_id)
                    ->where('id', $id)
                    ->first();
        return ResponseFormatter::success($datas,'OK');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $data['document_no']    = $request->document_no;
            $data['agent_id']       = auth()->user()->agent_id;
            $data['last_status']    = 'Open';
            $id = $this->model->create($data)->id;
            DB::commit();
            return ResponseFormatter::success($id,'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $validator = Validator::make($data, [
                'filename' => 'file|mimes:jpg,jpeg,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $timestamp = time();

            //file upload processing
            if(isset($request->filename)){
                //check old file
                $old = $this->model->where('id', $id)->get()->first();
                if($old->filename){
                    //delete old file
                    Storage::delete('document/agent/'.$old->filename);
                }

                $ext = $request->filename->getClientOriginalExtension();
                $ori_file = $request->filename->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->filename->storeAs('document/agent', $fileName);

                $data['filename'] = $fileName;
            }

            $data['document_no']     = $request->document_no;

            $object = $this->model->findOrFail($id);
            $object->fill($data);
            $object->save();

            DB::commit();
            return ResponseFormatter::success($id,'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function storeDetail(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $data['document_id']    = $id;
            $data['awb_no']         = $request->awb_no;
            $data['user_id']        = auth()->user()->agent_id;
            $id = $this->detailModel->create($data)->id;
            DB::commit();
            return ResponseFormatter::success($id,'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function deleteDetail(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $detail = $this->detailModel->where('id', $id)->get()->first();
            $status = $this->model->where('id', $detail->document_id)->get()->first();

            if($status->last_status == "Close"){
                throw new Exception('Dokumen ini sudah diclosing');
            }

            $this->detailModel->where('id',$id)->delete();
            DB::commit();
            return ResponseFormatter::success([],'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function closing(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = [];
            $check = $this->detailModel
                ->where('document_id', $id)->count();
            if($check > 0){
                $data['last_status']     = 'Close';
                $data['sent_date']       = Carbon::now()->toDateTimeString();
                $object = $this->model->findOrFail($id);
                $object->fill($data);
                $object->save();
            }else{
                throw new Exception('Dokumen ini tidak memiliki detail STT');
            }

            DB::commit();

            //send e-mail
            /*$body = [
                'title' => 'Test',
                'agent' => 'CV ABC'
            ];
        
            Mail::to("yudhatp@gmail.com")->send(new \App\Mail\DocumentMail($body));
            */

            return ResponseFormatter::success($id,'OK');
        
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}