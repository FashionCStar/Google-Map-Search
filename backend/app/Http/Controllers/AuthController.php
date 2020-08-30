<?php

namespace App\Http\Controllers;

use App\Mail\VerifyMail;
use App\VerifyUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use JWTFactory;
use JWTAuth;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    public function registerUser(Request $request)
    {
        $olduser = DB::table('users')
            ->where('email', $request->get('email'))
            ->first();
        if ($olduser && $olduser->verified != 1) {
            DB::table('verify_users')
                ->where('user_id', $olduser->id)
                ->delete();
            DB::table('users')
                ->where('id', $olduser->id)
                ->delete();
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password'=> 'required|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 404);
        }
        $user = User::create([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password'))
        ]);

        VerifyUser::create([
            'user_id' => $user->id,
            'token' => sha1(time())
        ]);
        Mail::to($user->email)->send(new VerifyMail($user));

        return Response::json(['success'=>true, 'user'=>$user]);
    }

    public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        if(isset($verifyUser) ){
            $user = $verifyUser->user;
            if(!$user->verified) {
                $verifyUser->user->verified = 1;
                $verifyUser->user->save();
                $status = "Your e-mail is verified. You can now login.";
            } else {
                $status = "Your e-mail is already verified. You can now login.";
            }
        } else {
            return Response::json(['success'=>false, 'msg'=>"Sorry your email cannot be identified."]);
//            return redirect('/login')->with('warning', "Sorry your email cannot be identified.");
        }
        return Response::json(['success'=>true, 'msg'=>$status]);
    }

    public function setUserRole(Request $request)
    {
//        $request = $request->all();
        $email=$request['email'];
        $userRole=$request['userRole'];
        $user = User::where('email',$email)->first();
        if ($user == null) {
            return response()->json(['error'=>'can\'t find user with this email'], 500);
        }
        $role = Role::findByName($userRole);
        if ($role == null) {
            return response()->json(['error'=>'can\'t find this user role'], 500);
        }
        $user->assignRole($role);
        return response()->json(['result'=>'success']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 404);
        }
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid email or password!'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Couldn\'t create token'], 500);
        }
        if (Auth::validate($credentials))
        {
            $user = Auth::getUser();
        }

        if ($user->verified != 1) {
            return response()->json(['error'=>'Your email is not verified, Try register again'], 401);
        }

        if ($user->is_activated != 1) {
            return response()->json(['error'=>'Your Account is deactivated by admin, Contact to admin'], 401);
        }

        DB::table('users')->where('id', $user->id)->update(array(
            'logined'=>1,
        ));
        return response()->json(['success'=>true, 'token'=>$token, 'user'=>$user]);
    }

    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 404);
        }
        DB::table('users')->where('id', $request->get('id'))->update(array(
            'logined'=>0,
        ));
//        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }



    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
