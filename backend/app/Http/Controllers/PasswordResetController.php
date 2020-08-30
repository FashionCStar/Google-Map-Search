<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Mail\VerifyMail;
use App\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Validator;
use Response;

use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = DB::table('users')
            ->where('email', $request->get('email'))
            ->first();
        if (!$user) {
            return response()->json([
                'msg' => `We can't find a user with that e - mail address .`], 404);
        }
            
        $passwordReset = PasswordReset::create([
                'user_id' => $user->id,
                'email' => $request->get('email'),
                'token' => sha1(time())
            ]
        );
        if ($user && $passwordReset) {
//            Mail::to($user->email)->send(new VerifyMail($user));
            Mail::to($user->email)->send(new ResetPasswordMail($user));
        }

        return response()->json([
            'message' => 'We have e - mailed your password reset link!'
        ]);
    }
    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        if (!$passwordReset)
            return response()->json([
                'msg' => 'This password reset token is invalid . '
            ], 404);
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'msg' => 'This password reset token is invalid . '
            ], 404);
        }
        return response()->json($passwordReset);
    }
     /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required | string | email',
            'password' => 'required | string',
            'token' => 'required | string'
        ]);
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset)
            return response()->json([
                'msg' => 'This password reset token is invalid . '
            ], 404);
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return response()->json([
                'msg' => 'We can`t find a user with that e-mail address.'
            ], 404);
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        return response()->json($user);
    }

}
