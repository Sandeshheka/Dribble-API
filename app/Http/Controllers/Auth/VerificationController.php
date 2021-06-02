<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{




    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      
        // $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
    public function verify(Request $request, User $user)
    {
        //check if the url is a valid sugned url

        if(! URL::hasValidSignature($request)){
            return response()->json(["errors" => [
                "message" => "Invalid Verification Link"
            ]], 422);
        }

        // check if the user has already verified account
        if($user->hasVerifiedEmail())
        {
            return response()->json(["errors" => [
                "message" => "Email Address Already Verified"
            ]],  422);
        }
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email Successfully Verified'], 200);
        }
  

    public function resend(Request $request)
    {
        $this->validate($request, [
            'email' => ['required','email']
        ]);

        $user = User::where('email', $request->email)->first();
        if(! $user){
            return response()->json(["error" => [
                "email" => "No User Could Be Found With This Email Address"
            ]], 422);
        }
        if($user->hasVerifiedEmail())
        {
            return response()->json(["errors" => [
                "message" => "Email Address Already Verified"
            ]],  422);
        }
        
        $user->sendEmailVerificationNotification();
        return response()->json(['status' => "verification link resent"]);
    }
}
