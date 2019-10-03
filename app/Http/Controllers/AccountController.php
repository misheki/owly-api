<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Organization;
use App\User;
use Log;
use Carbon\Carbon;
use Auth;

class AccountController extends Controller
{

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
    }

    public function merchant() {
        try {
			
            $user = $this->user;

            $today = Carbon::now()->toFormattedDateString();

            if($user->status == 'ACTIVE'){
                return response()->json(['result' => 'GOOD', 'user' => $user, 'merchant' => $user->organization, 'today' => $today]);
            }

			return response()->json(['result' => 'NO_ACCESS']);
    	}
    	catch (\Exception $e) {
    		Log::error("Exc caught while AccountController@merchant: " . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
    	}
    }

}