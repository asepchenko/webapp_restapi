<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Services\BaseService;
use App\Helpers\ResponseFormatter;
use Exception;

use App\Repositories\OrderAgentRepository;
use App\Repositories\OrderCostAgentRepository;
use App\Repositories\AgentRepository;
use App\Repositories\AgentMasterPriceRepository;

class OrderAgentService extends BaseService
{
    protected $repo, $repoOrderCostAgent, $repoAgentMasterPrice, $repoAgent;

    public function __construct(
        OrderAgentRepository $repo,
        AgentRepository $repoAgent,
        OrderCostAgentRepository $repoOrderCostAgent,
        AgentMasterPriceRepository $repoAgentMasterPrice
    ) {
        parent::__construct();
        $this->repo = $repo;
        $this->repoAgent = $repoAgent;
        $this->repoOrderCostAgent = $repoOrderCostAgent;
        $this->repoAgentMasterPrice = $repoAgentMasterPrice;
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

            //validating
            /*$validator = Validator::make($data, [
                'orderagent_name' => 'required|max:255|unique:orderagents,orderagent_name'
            ]);
    
            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }*/

            if(substr($request->agent_id,0,1) == "A"){
                //check if agent has active mou
                $mou = $this->repoAgent->getActiveCountData(ltrim($request->agent_id,'A'));
                if ($mou <= 0) {
                    return ResponseFormatter::error('','Agent ini tidak memiliki MOU Aktif','400');
                }

                //check if location id & service id already exist
                $check = $this->repo->checkAlreadyExist($request->order_number, ltrim($request->agent_id,'A'),'A');

                if($check){
                    throw new Exception('Data agent di transaksi ini sudah tersedia');
                }
                $data['agent_id'] = ltrim($request->agent_id,'A');
                $data['branch_id'] = NULL;

                //get agent master price
                $price = $this->repoAgentMasterPrice->getPriceByOrderNumber($request->order_number, ltrim($request->agent_id,'A'), $request->origin, $request->destination);
                if($price == 0){
                    throw new Exception('Data harga agent di transaksi ini tidak tersedia');
                }
                //cleansing data
                $price = str_replace('.','',$price);
                $price = str_replace(',','.',$price);
                
                //$price = 0;

                //insert ke order cost agent
                $data_cost = [];
                $data_cost['order_number']  = $request->order_number;
                $data_cost['agent_id']      = ltrim($request->agent_id,'A');
                $data_cost['price']         = $price;
                $data_cost['user_id']       = auth()->user()->id;
                $this->repoOrderCostAgent->create($data_cost);

            }else{
                //check if location id & service id already exist
                $check = $this->repo->checkAlreadyExist($request->order_number, ltrim($request->agent_id,'B'),'B');

                if($check){
                    throw new Exception('Data cabang agent di transaksi ini sudah tersedia');
                }
                $data['agent_id'] = NULL;
                $data['branch_id'] = ltrim($request->agent_id,'B');
            }

            $sq = $this->repo->isSameSequence($request->order_number, $request->sequence);

            if($sq){
                throw new Exception('Nomor urut tidak boleh sama');
            }

            //$data['sequence'] = $this->repo->getSequence($request->order_number);
            $data['user_id']  = auth()->user()->id;
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
                'orderagent_name' => ['required','max:255',Rule::unique('orderagents', 'orderagent_name')->ignore($id)]
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
            $agent_id = $this->repo->getAgentID($id);
            $order_number = $this->repo->getOrderNumber($id);
            //$agent_id = $this->repo->getAgentID($id, $order_number);
            $this->repoOrderCostAgent->deleteByOrderNumberAgentID($order_number, $agent_id);
            $this->repo->delete($id);

            DB::commit();
            return ResponseFormatter::success('','OK');
        } catch (Exception $exc) {
            DB::rollBack();
            return ResponseFormatter::error('', $exc->getMessage(),'400');
        }
    }
}
