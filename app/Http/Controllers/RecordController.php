<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Worker;
use App\Scan;
use App\User;
use Log;
use Carbon\Carbon;
use Validator;
use Auth;

class RecordController extends Controller
{

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
    }

    public function addConfirm(Request $request) {
        try {
			
            $user = $this->user;

            $validator = Validator::make($request->all(), [
	            'qr_code' => 'required|min:14'
            ]);
            
	        if ($validator->fails()) {
	            return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }
                 
            //dissect QR code OWLY#<ORG CODE>#<WORKER CODE>
            $qr_code = explode('#', $request->qr_code, 3);

            if($qr_code[0] != "OWLY")
                return response()->json(['result' => 'INVALIDQR_PREFIX']);

            if($qr_code[1] != $user->organization->code)
                return response()->json(['result' => 'INVALIDQR_ORGCODE']);

            $worker = Worker::where('worker_code', $qr_code[2])
                            ->where('organization_id', $user->organization->id)->first();

            if(!is_null($worker)){
                if($worker->status == 'ACTIVE'){
                    $scan_dt = Carbon::now();
                    return response()->json(['result' => 'GOOD', 'name' => $worker->name, 'scan_dt' => $scan_dt]);
                }
                else {
                    return response()->json(['result' => 'INVALIDQR_WORKERINACTIVE']);
                }
            }
           
            return response()->json(['result' => 'INVALIDQR_NOSUCHWORKER']);
			
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@addConfirm: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }
    
    public function add(Request $request) {

    	try {
			
            $user = $this->user;

            $validator = Validator::make($request->all(), [
                'qr_code' => 'required|min:14',
                'scan_dt' => 'required'
            ]);
            
	        if ($validator->fails()) {
	            return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }
                 
            //dissect QR code OWLY#<ORG CODE>#<WORKER CODE>
            $qr_code = explode('#', $request->qr_code, 3);

            if($qr_code[0] != "OWLY")
                return response()->json(['result' => 'INVALIDQR_PREFIX']);

            if($qr_code[1] != $user->organization->code)
                return response()->json(['result' => 'INVALIDQR_ORGCODE']);

            $worker = Worker::where('worker_code', $qr_code[2])
                            ->where('organization_id', $user->organization->id)->first();

            if(!is_null($worker)){
                if($worker->status == 'ACTIVE'){
                    $scan = Scan::create([
                        'user_id' => $user->id,
                        'worker_code' => $worker->worker_code,
                        'scan_dt' => $request->scan_dt
                    ]);

                    $scan->setAttribute('name', $worker->name);

                    return response()->json(['result' => 'GOOD', 'data' => $scan]);
                }
                else {
                    return response()->json(['result' => 'INVALIDQR_WORKERINACTIVE']);
                }
            }
           
            return response()->json(['result' => 'INVALIDQR_NOSUCHWORKER']);
			
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@add: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    // Can only edit your own scans
    public function edit($id) {
        try {
			
            $user = $this->user;
            
            $scan = Scan::where('id', $id)->where('user_id', $user->id)->first();

            if(!is_null($scan)){
                return response()->json(['result' => 'GOOD', 'data' => $scan]);
            }

			return response()->json(['result' => 'NO_ACCESS']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@edit: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function update(Request $request, $id) {
        try {
			
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [
                'scan_dt' => 'required',
                'edit_remarks' => 'required'
            ]);
            
	        if ($validator->fails()) {
	            return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }

            $scan = Scan::where('id', $id)->where('user_id', $user->id)->first();

            if(!is_null($scan)){
                $scan->update([
                    'scan_dt' => $request->scan_dt,
                    'edit_remarks' => $request->edit_remarks,
                    'edited_by' => $user->id,
                    'edited_at' => Carbon::now()
                ]);

                return response()->json(['result' => 'GOOD', 'data' => $scan]);
            }
            
			return response()->json(['result' => 'NOTFOUND']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@update: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function delete($id) {
        try {
			
            $user = $this->user;

            $scan = Scan::where('id', $id)->where('user_id', $user->id)->first();
            
            if(!is_null($scan)){
                $scan->delete();
                return response()->json(['result' => 'GOOD']);
            }

			return response()->json(['result' => 'NOTFOUND']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@delete " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function daily($date) {
        try {

            $user = $this->user;
			
            // Get all scans of all users under the same organization
            $users = User::where('organization_id', $user->organization_id)->get();
            
            $scans = Scan::whereDate('scan_dt', $date)->whereIn('user_id', $users)->get();

            $data = array();
            if(!is_null($scans)){
                foreach($scans as $scan){

                    $worker = Worker::where('worker_code', $scan->worker_code)->firstOrFail();

                    if(!in_array($scan->worker_code, array_column($data, 'id'))){
                        array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "out"=>"No entry"));
                    }
                    else{
                        foreach($data as &$d){
                            if($d['id'] == $scan->worker_code){
                                if($d['out'] == "No entry"){
                                    $d['out'] = Carbon::parse($scan->scan_dt)->format('g:i:s A');
                                }        
                                else{
                                    array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "out"=>"No entry"));
                                }
                                break;
                            }       
                        }
                    }    
                }
                return response()->json(['result' => 'GOOD', 'data' => $data]);
            }
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@daily: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

}