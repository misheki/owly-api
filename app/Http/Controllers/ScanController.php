<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;

class ScanController extends Controller
{

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
    }
    
    public function scan(Request $request) {

    	try {
			
            $user = $this->user;
            

			return response()->json(['result' => 'GOOD', 'data' => $user]);
    	}
    	catch (\Exception $e) {
    		Log::error($e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

}