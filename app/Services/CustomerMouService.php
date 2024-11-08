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
use PDF,Exception;

use App\Repositories\CustomerRepository;
use App\Repositories\CustomerPicRepository;
use App\Repositories\CustomerMouRepository;

class CustomerMouService extends BaseService
{
    protected $repo, $custRepo, $custPicRepo;

    public function __construct(
        CustomerMouRepository $repo,
        CustomerRepository $custRepo,
        CustomerPicRepository $custPicRepo
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->custRepo = $custRepo;
        $this->custPicRepo = $custPicRepo;
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

    public function indexByCustomerID($id)
    {
        $data = $this->repo->getIndexByCustomerID($id);
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

    public function namaHari($tgl) {
        $tmp = date_format($tgl,"w");
        $hari = array (1 =>   'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');
        return $hari[$tmp];
    }

    public function namaBulan($tgl) {
        $tmp = date_format($tgl,"n");
        $bulan = array (1 =>   'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
        return $bulan[$tmp];
    }

    public function terbilang($nilai) {
		$nilai = abs($nilai);
		$huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = $this->terbilang($nilai - 10). " Belas";
		} else if ($nilai < 100) {
			$temp = $this->terbilang($nilai/10)." Puluh". $this->terbilang($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . $this->terbilang($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = $this->terbilang($nilai/100) . " Ratus" . $this->terbilang($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . $this->terbilang($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = $this->terbilang($nilai/1000) . " Ribu" . $this->terbilang($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = $this->terbilang($nilai/1000000) . " Juta" . $this->terbilang($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = $this->terbilang($nilai/1000000000) . " Milyar" . $this->terbilang(fmod($nilai,1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = $this->terbilang($nilai/1000000000000) . " Trilyun" . $this->terbilang(fmod($nilai,1000000000000));
		}     
		return $temp;
	}

    public function create(Request $request, array $data)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($data, [
                'mou_file' => 'file|mimes:doc,docx,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $timestamp = time();

            //file upload processing
            if(isset($request->mou_file) && $request->is_generate_mou == "N"){
                $ext = $request->mou_file->getClientOriginalExtension();
                $ori_file = $request->mou_file->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->mou_file->storeAs('customer/mou', $fileName);

                $data['mou_file'] = $fileName;
            }else{
                //check must have customer PIC
                $isHavePIC = $this->custPicRepo->getActiveData($request->customer_id);
                if (count($isHavePIC) <= 0) {
                    throw new Exception("Tidak bisa melakukan generate MOU karena customer ini tidak memiliki PIC yg aktif");
                }

                $cust_name = $this->custRepo->getCustomerName($request->customer_id);
                $fileName = $timestamp. "_" . $cust_name . ".pdf";
                $data['mou_file'] = $fileName;
            }
            
            $data['user_id'] = auth()->user()->id;
            $result = $this->repo->createWithID($data);
            $mou_number = $this->repo->getMouNumber($result);
            
            //create pdf
            if(!isset($request->mou_file) && $request->is_generate_mou == "Y"){
                //generate mou file
                $date = date_create($request->mou_start_date);
                $tgl_mou = date_format($date,"d-m-Y");

                $tgl = date_format($date,"d");
                $tahun = date_format($date,"Y");

                $data_pdf = [
                    'customer' => $this->custRepo->show($request->customer_id),
                    'tgl_mou' => $tgl_mou,
                    'hari' => $this->namaHari($date),
                    'tgl_bilang' => $this->terbilang($tgl),
                    'tgl' => $tgl,
                    'bulan' => $this->namaBulan($date),
                    'tahun_bilang' => $this->terbilang($tahun),
                    'tahun' => $tahun,
                    'mou_number' => $mou_number,
                    'pic' => $this->custPicRepo->getIndexByCustomerID($request->customer_id)
                ];
                
                $pdf = PDF::loadView('customers.mou', $data_pdf)->setPaper('a4', 'potrait');
                $content = $pdf->download()->getOriginalContent();

                $cust_name = $this->custRepo->getCustomerName($request->customer_id);
                $fileName = $timestamp. "_" . $cust_name . ".pdf";
                Storage::put('customer/mou/'.$fileName,$content);
            }

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
                'mou_file' => 'file|mimes:doc,docx,pdf|max:5128'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            //get file first
            $recent_mou = $this->repo->getMouFileName($id);

            //file upload processing
            if(isset($request->mou_file)){
                $ext = $request->mou_file->getClientOriginalExtension();
                $timestamp = time();
                $ori_file = $request->mou_file->getClientOriginalName();
                $ori_filename = pathinfo($ori_file, PATHINFO_FILENAME);
                $fileName = $timestamp. "_" . $ori_filename . "." . $ext;
                $request->mou_file->storeAs('customer/mou', $fileName);

                $data['mou_file'] = $fileName;

                //delete old file
                Storage::delete('customer/mou/'.$recent_mou);
            }else{
                if($request->is_generate_mou == "Y"){
                    //generate mou file
                    $data['mou_file'] = 'generated';

                    //delete old file
                    Storage::delete('customer/mou/'.$recent_mou);
                }
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

            //get file first
            $file = $this->repo->getMouFileName($id);
            //update first
            $data['deleted_by'] = auth()->user()->id;
            $this->repo->update($data, $id);
            $this->repo->delete($id);

            //because softdelete, don't delete file
            //delete file
            //Storage::delete('customer/mou/'.$file);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }

    public function deleteByCustID($id)
    {
        DB::beginTransaction();
        try {

            //get list mou first
            $tmp_file = $this->repo->getIndexByCustomerID($id);
            foreach ($tmp_file as $value) {
                $this->repo->delete($value->id);
            
                //delete file
                Storage::delete('customer/mou/'.$value->mou_file);
            }
            

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
