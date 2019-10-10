<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Worker;
use App\Scan;
use App\User;
use App\Organization;
use App\Report;
use Log;
use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{

    public function __construct()
    {
        
    }

    public function daily() {
        try {

            $user = $this->user;

            $organizations = Organization::where('status', 'ACTIVE')->get();
			
            // Get all scans of all users under the same organization
            $users = User::where('organization_id', $user->organization_id)->get();
            
            $scans = Scan::whereDate('scan_dt', $date)->whereIn('user_id', $users)->get();

            $data = array();
            if(!is_null($scans)){
                foreach($scans as $scan){

                    $worker = Worker::where('worker_code', $scan->worker_code)->firstOrFail();

                    if(!in_array($scan->worker_code, array_column($data, 'id'))){
                        array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "out_id"=>null, "out"=>"No entry"));
                    }
                    else{
                        foreach($data as &$d){
                            if($d['id'] == $scan->worker_code){
                                if($d['out'] == "No entry"){
                                    $d['out_id'] = $scan->id;
                                    $d['out'] = Carbon::parse($scan->scan_dt)->format('g:i:s A');
                                }        
                                else{
                                    array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "out_id"=>null, "out"=>"No entry"));
                                }
                                break;
                            }       
                        }
                    }    
                }
                return response()->json(['result' => 'GOOD', 'data' => $data, 'editable' => $editable]);
            }
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while RecordController@daily: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

    public function periodic() {

    }

}