<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Worker;
use Log;
use Carbon\Carbon;
use Validator;
use Auth;

class WorkerController extends Controller
{

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
    }
    
    public function add(Request $request) {

    	try {
			
            $user = $this->user;

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'status' => 'required'
            ]);

	        if ($validator->fails()) {
	            return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }

            $worker_code = $this->generateWorkerCode();
               
            //create QR code OWLY#<ORG CODE>#<WORKER CODE>
            $worker = Worker::create([
                'organization_id' => $user->organization_id,
                'worker_code' => $worker_code,
                'name' => $request->name,
                'code' => $request->code,
                'address' => $request->address,
                'started_at' => $request->started_at,
                'staff_id' => $request->staff_id,
                'qr' => 'OWLY#' . $user->organization->code . '#' . $worker_code,
                'status' => $request->status
            ]);

            return response()->json(['result' => 'GOOD', 'data' => $worker]);
			
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while WorkerController@add: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function edit($id) {
        try {
			
            $user = $this->user;
            
            $worker = Worker::where('id', $id)->where('organization_id', $user->organization_id)->first();

            if(!is_null($worker)){
                return response()->json(['result' => 'GOOD', 'data' => $worker]);
            }

			return response()->json(['result' => 'NO_ACCESS']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while WorkerController@edit " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function update(Request $request, $id) {
        try {
			
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'status' => 'required'
            ]);
            
	        if ($validator->fails()) {
	            return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }

            $worker = Worker::where('id', $id)->where('organization_id', $user->organization_id)->first();

            if(!is_null($worker)){
                $worker->update([
                    'name' => $request->name,
                    'code' => $request->code,
                    'address' => $request->address,
                    'started_at' => $request->started_at,
                    'ended_at' => $request->ended_at,
                    'staff_id' => $request->staff_id,
                    'status' => $request->status
                ]);

                return response()->json(['result' => 'GOOD']);
            }
            
			return response()->json(['result' => 'NOTFOUND']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while WorkerController@update " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function delete($id) {
        try {
			
            $user = $this->user;

            $worker = Worker::where('id', $id)->where('organization_id', $user->organization_id)->first();
            
            if(!is_null($worker)){
                $worker->delete();
                return response()->json(['result' => 'GOOD']);
            }

			return response()->json(['result' => 'NOTFOUND']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while WorkerController@delete " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    protected function generateWorkerCode()
    {
        $int1 = rand(0,25);
        $int2 = rand(0,25);
        $a_z = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $rand_letters = $a_z[$int1] . $a_z[$int2];
        $worker_code = $rand_letters . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);

        if(is_null(Worker::where('worker_code', $worker_code)->first())){
            return $worker_code;
        }
        else{
            $this->generateWorkerCode();
        }       
    }

}