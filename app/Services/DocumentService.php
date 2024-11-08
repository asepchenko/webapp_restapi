<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; //file upload
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail; //send e-mail
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;
use Exception;

use App\Repositories\DocumentRepository;

class DocumentService extends BaseService
{
    protected $repo;

    public function __construct(
        DocumentRepository $repo
    ) {
        parent::__construct();
        $this->repo = $repo;
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

            $validator = Validator::make($data, [
                'filename' => 'required|file|mimes:jpg,jpeg,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //file upload processing
            if(isset($request->filename)){
                $timestamp = time();
                $ext = $request->filename->getClientOriginalExtension();
                $ori_file = $request->filename->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->filename->storeAs('document/agent', $fileName);
                $data['filename'] = $fileName;
            }

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
            $validator = Validator::make($data, [
                'filename' => 'file|mimes:doc,docx,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //get file first
            $recent_file = $this->repo->getFileName($id);

            //file upload processing
            if(isset($request->filename)){
                $ext = $request->filename->getClientOriginalExtension();
                $timestamp = time();
                $ori_file = $request->filename->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->filename->storeAs('document/agent', $fileName);

                $data['filename'] = $fileName;

                //delete old file
                Storage::delete('document/agent/'.$recent_file);
            }

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

    public function closing(Request $request, array $data)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->getRecipient($request->id);
            if($check){
                throw new Exception('Data dokumen ini sudah diproses');
            }

            $data['recipient_date']        = Carbon::now()->toDateTimeString();
            $data['recipient_user_id']     = auth()->user()->id;
            $result = $this->repo->update($data, $request->id);
            DB::commit();

            return ResponseFormatter::success($result,'OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
