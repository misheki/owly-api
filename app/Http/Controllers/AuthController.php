<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Client;
use Carbon\Carbon;

use App\User;
use Hash;
use Validator;
use Log;

class AuthController extends AccessTokenController
{
    public function auth(ServerRequestInterface $request)
    {
        try {
            $tokenResponse = parent::issueToken($request);
            $token = $tokenResponse->getContent();
            $tokenInfo = json_decode($token, true);

            $username = $request->getParsedBody()['username'];
            $user = User::whereEmail($username)->first();

            if (!is_null($user)) {

                if($user->status == 'INACTIVE') {
                    return response()->json(['error' => 'inactive_account', 'message' => 'Inactive Account']);
                }

                $tokenInfo = collect($tokenInfo);
                $tokenInfo->put('user', $user); // add info to token response

            }       

            return response()->json($tokenInfo);
        }
        catch (\Exception $e) {
            Log::error('Exc caught while AuthController@auth: ' . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            if (!$this->checkClient($request->client_id, $request->client_secret)) {
                return response()->json(['result' => 'INVALIDCLIENT']);
            }

            $validator = Validator::make($request->all(),[
                'email' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }

            $user = User::whereEmail($request->email)->first();

            if (is_null($user)) {
                return response()->json(['result' => 'NOTFOUND', 'msg' => 'This email address cannot be found in the system.']);
            }

            // Send email to user

            return response()->json(['result' => 'GOOD']);
        }
        catch (\Exception $e) {
            Log::error('Exc caught while AuthController@forgotPassword: ' . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            if (!$this->checkClient($request->client_id, $request->client_secret)) {
                return response()->json(['result' => 'INVALIDCLIENT']);
            }
            
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|confirmed|password_validation|min:8|max:12'
            ]);

            if ($validator->fails()) {
                return response()->json(['result' => 'ERROR', 'msg' => $validator->errors()->first()]);
            }

            $user = User::whereEmail($request->email)->first();
            $user->update(['password' => Hash::make($request->password)]);
            
            return response()->json(['result' => 'GOOD']);
        }
        catch (\Exception $e) {
            Log::error('Exc caught while AuthController@resetPassword: ' . $e->getMessage());
            return response()->json(['result' => 'ERROR', 'msg' => $e->getMessage()]);
        }
    }

}