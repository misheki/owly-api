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
                $count = 0;
                if(!is_null($scans)){
                    foreach($scans as $scan){

                        $worker = Worker::where('worker_code', $scan->worker_code)->firstOrFail();

                        if(!in_array($scan->worker_code, array_column($data, 'worker_code'))){
                            array_push($data, array("id"=>$count, "worker_code"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "scan_in"=>$scan->scan_dt, "out_id"=>null, "out"=>"No entry", "scan_out"=>null));
                            $count++;
                        }
                        else{
                            foreach($data as &$d){
                                if($d['worker_code'] == $scan->worker_code){
                                    if($d['out'] == "No entry"){
                                        $d['out_id'] = $scan->id;
                                        $d['out'] = Carbon::parse($scan->scan_dt)->format('g:i:s A');
                                        $d['scan_out'] = $scan->scan_dt;
                                    }
                                    else{
                                        array_push($data, array("id"=>$count, "worker_code"=>$scan->worker_code, "name"=>$worker->name, "in_id" =>$scan->id, "in"=>Carbon::parse($scan->scan_dt)->format('g:i:s A'), "scan_in"=>$scan->scan_dt, "out_id"=>null, "out"=>"No entry", "scan_out"=>null));
                                        $count++;
                                    }
                                    break;
                                }       
                            }
                        }    
                    }

                    $totals = $this->calculateHours($data);
                    return response()->json(['result' => 'GOOD', 'records' => $data, 'totals' => $totals]);
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

    protected function calculateHours($records) {

        try{
            $data = array();
            $count = 0;

            foreach($records as $record){
                return $record;
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $record['scan_in']);
                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $record['scan_out']);
                $diff_in_seconds = $to->diffInSeconds($from);

                $hours = floor($diff_in_seconds / 3600);
                $diff_in_seconds -= $hours * 3600;
                $minutes = floor($diff_in_seconds / 60);
                $hours = $hours + ($minutes/60);

                if($hours > 8){
                    $regular = 8;
                    $overtime = $hours - 8;
                }
                else{
                    $regular = $hours;
                    $overtime = 0;
                }

                if(!in_array($record['worker_code'], array_column($data, 'worker_code'))){
                    array_push($data, array("id"=>$count, "worker_code"=>$record['worker_code'], "regular"=>$regular, "overtime"=>$overtime));
                    $count++;
                }
                else{
                    foreach($data as &$d){
                        $r = $d['regular'] + $regular;
                        if($r > 8){
                            $d['regular'] = 8;
                            $d['overtime'] = $d['overtime'] + ($r - 8) + $overtime;
                        }
                        else{
                            $d['regular'] = $r;
                            $d['overtime'] = $d['overtime'] + $overtime;
                        }
                    }
                }
            }

            return $data;
        }
        catch (\Exception $e) {
            Log::error("Exc caught while RecordController@calculateHours: " . $e->getMessage());
            return null;
        }
    }

}