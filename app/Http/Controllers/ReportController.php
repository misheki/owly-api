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

    public function daily($subdays) {
        try {

            // $yesterday = Carbon\Carbon::yesterday();
            $yesterday = Carbon::now()->subDays($subdays);
            $organizations = Organization::where('status', 'ACTIVE')->get();

            foreach($organizations as $organization){
                // Get all users under the same organization
                $users = User::where('organization_id', $organization->id)->get();

                // Get all scans of these users yesterday
                $scans = Scan::whereDate('scan_dt', $yesterday)->whereIn('user_id', $users)->get();

                $data = array();
                if(!is_null($scans)){
                    foreach($scans as $scan){

                        $worker = Worker::where('worker_code', $scan->worker_code)->firstOrFail();

                        if(!in_array($scan->worker_code, array_column($data, 'id'))){
                            array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "scan_in"=>$scan->scan_dt, "out_id"=>null, "out"=>"No entry", "scan_out"=>null, "regular"=>0, "overtime"=>0));
                        }
                        else{
                            foreach($data as &$d){
                                if($d['id'] == $scan->worker_code){
                                    if($d['out'] == "No entry"){
                                        $d['out_id'] = $scan->id;
                                        $d['out'] = Carbon::parse($scan->scan_dt)->format('g:i:s A');
                                        $d['scan_out'] = $scan->scan_dt;
                                        
                                        $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $d['scan_in']);
                                        $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $d['scan_out']);
                                        $diff_in_hours = $to->diffInHours($from);
                                        if($diff_in_hours > 8){
                                            $regular = 8;
                                            $overtime = $diff_in_hours - 8;
                                        }
                                        else{
                                            $regular = $diff_in_hours;
                                            $overtime = 0;
                                        }
                                            
                                        $d['regular'] = $regular;
                                        $d['overtime'] = $overtime;
                                    }        
                                    else{
                                        array_push($data, array("id"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "scan_in"=>$scan->scan_dt, "out_id"=>null, "out"=>"No entry", "scan_out"=>null, "regular"=>0, "overtime"=>0));
                                    }
                                    break;
                                }       
                            }
                        }    
                    }
                    return response()->json(['result' => 'GOOD', 'data' => $data]);
                }
                return response()->json(['result' => 'NORECORDS']);
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