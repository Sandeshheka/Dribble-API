<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
  

    use AuthenticatesUsers;


    public function attemptLogin(Request $request)
    {
          // attempt to issue a token to the user based on the login credentials
          $token = $this->guard()->attempt($this->credentials($request));

          if( ! $token)
          {
              return false;
          }
  
          // get authenticated user
  
          $user = $this->guard()->user();
  
          if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
              return false;
          }
  
          // set token to users
  
          $this->guard()->setToken($token);
          return true;
    }
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        //get the token from authentication guard JWt
        $token = (string)$this->guard()->getToken();

        //extract the expiry date of the token
        $expiration = $this->guard()->getPayLoad()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration
        ]);


    }
    
    protected function sendFailedLoginResponse(){
        $user = $this->guard()->user();
        if($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()){
                return response()->json(["errors" =>
                [
                    "verification" => "you need to verify your email account"
                ]
                ]);

        }

        throw ValidationException::withMessages ([
            $this->username() => "Invalid Email||Password"
        ]);
    }


    public function logout()
    {
        $this->guard()->logout();
        return response()->json(["message" => "Logout successfully"]);
    }


}
