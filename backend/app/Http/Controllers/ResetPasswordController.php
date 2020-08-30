<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Mail\ResetPasswordMail;
use Mail;
use DB;
use Carbon;
use App\User;

use Validator;
use Response;
use JWTAuth;

class ResetPasswordController extends Controller
{
    //
    public function sendPasswordResetEmail(Request $request) {
        if (!$this->validateEmail($request->email)) {
            return response()->json(['data'=>'Email doesn\'t found on our database'], 404);
        }

        $this->send($request->email);
        return response()->json(['data'=>'Reset Email is sent successfully, Please check your inbox'], 200);
    }

    public function validateEmail($email) {
        return !!User::where('email', $email)->first();
    }

    public function send($email) {
        $token = $this->createToken($email);
        Mail::to($email)->send(new ResetPasswordMail($token));
    }

    public function createToken($email) {
        $oldToken = DB::table('password_resets')->where('email', $email)->first()->token;
        if ($oldToken){
            return $oldToken;
        }
        $token = str_random(60);
        $this->saveToken($token, $email);
        return $token;
    }

    public function saveToken($token, $email) {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function resetPassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        return $this->getPasswordResetTableRow($request) ? $this->changePassword($request) : $this->tokenNotFoundResponse();

//        $this->send($request->email);
//        return response()->json(['data'=>'Reset Email is sent successfully, Please check your inbox'], 200);
    }

    private function getPasswordResetTableRow($request) {
        return DB::table('password_resets')->where(['email'=>$request->email, 'token'=>$request->resetToken]);
    }

    private function changePassword($request) {
        $user = User::whereEmail($request->email)->first();
        $user->update(['password'=>bcrypt($request->password)]);

//        $user = User::first();
        $token = JWTAuth::fromUser($user);

        return response()->json(['data'=>'Password Successfully Changed', 'token'=>$token], 201);
    }

    private function tokenNotFoundResponse() {
        return response()->json(['error'=>'Token or Email is not correct'], 404);
    }
}
