<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Response;

use App\Http\Controllers\Controller;
use JWTFactory;
use JWTAuth;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;



class ManageUsersController extends Controller
{
    //
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required',
            'password'=> 'required',
            'firstName'=> 'required',
            'lastName'=> 'required',
            'phone'=> 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        }

        try {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => bcrypt($request->get('password')),
                'avatar' => $request->get('avatar'),
                'firstName' => $request->get('firstName'),
                'lastName' => $request->get('lastName'),
                'middleName' => $request->get('middleName'),
                'phone' => $request->get('phone'),
                'active_status' => 0,
            ]);

            $role_ids = $request->get('role_ids'); // get  Roles from post request
            foreach ($role_ids as $role_id) {
                $role = Role::find($role_id);
                $user->roles()->attach($role);
            }
            $token = JWTAuth::fromUser($user);

            return Response::json(['result' => 'success', 'user' => $user, 'token' => $token]);
        } catch (JWTException $e) {
            return Response::json(['error' => 'This email is already registered'], 500);
        }
    }

    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'username' => 'required',
            'email' => 'required|string|email|max:255'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::find($request->get('id'));
        if ($user) {
            $user->update([
                'email' => $request->get('email'),
                'username' => $request->get('username'),
                'avatar_path' => $request->get('avatar_path'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'phone' => $request->get('phone'),
            ]);
        } else {
            return Response::json(['success'=>false, 'msg'=>'User does not exist']);
        }
        $token = JWTAuth::fromUser($user);

        return Response::json(['success'=>true, 'msg'=>'User Successfully Updated', 'user'=>$user, 'token'=>$token]);
    }

    public function activeUser(Request $request) {
        $user = User::find($request->get('id'));
        $user->update(['is_activated'=>$request->get('is_activated')]);

        $user = User::first();
        $token = JWTAuth::fromUser($user);

        return response()->json(['data'=>'User Successfully Changed', 'token'=>$token], 201);
    }

    public function deleteUser($id) {
        $user = User::find($id);
        try {
            DB::table('verify_users')
                ->where('user_id', $user->id)
                ->delete();
            $user->delete();
            return response()->json(['success'=>'User Successfully Removed']);
        } catch(\Exception $e) {
            return response()->json(['error'=>$e], 500);
        }
    }

    public function getUsers() {
        try {
//            $user = JWTAuth::toUser(JWTAuth::parseToken());
            $page = Input::get('pageNo') != null ? Input::get('pageNo') : 1;
            $limit = Input::get('numPerPage') != null ? Input::get('numPerPage') : 30;
            $role_name = Input::get('rolename') != null ? Input::get('rolename') : null;

            if ($role_name == null) {
                $totalCount = count(User::all());
                $users = User::orderBy('username', 'asc')->skip(($page - 1) * $limit)->take($limit)->get();
            } else {
                $role = Role::findByName($role_name);
                $totalCount = count(User::role($role)->get());
                $users = User::role($role)->skip(($page - 1) * $limit)->take($limit)->get();
            }
            if ($totalCount == 0) {
                return response()->json(['totalCount'=>$totalCount, 'userdata'=>[]], 200);
            } else {
//                foreach ($users as $user) {
//                    $roles = $this->getUserRoles($user);
//                    $user->push($roles);
//                    $userdatas[] = $user;
//                }
                return response()->json(['totalCount'=>$totalCount, 'userdata'=>$users], 200);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in'], 500);
        }
    }

    public function getUserByID($id) {
        try {
            $user = User::find($id);
            $posts = DB::table('posts')
                ->join('discussions', 'discussions.id', '=', 'posts.discussion_id')
                ->join('categories', 'categories.id', '=', 'discussions.category_id')
                ->select('posts.*', 'discussions.title as topic_name', 'categories.name as category_name')
                ->where('posts.user_id', $id)
                ->orderBy('posts.created_at', 'desc')
                ->limit(3)
                ->get();
            $topics = DB::table('discussions')
                ->join('categories', 'categories.id', '=', 'discussions.category_id')
                ->select('discussions.*', 'categories.name as category_name')
                ->where('discussions.user_id', $id)
                ->orderBy('discussions.created_at', 'desc')
                ->limit(3)
                ->get();
            $user->posts = $posts;
            $user->topics = $topics;
            return response()->json(['success'=>true, 'data'=>$user], 201);
            }
        catch(\Exception $e) {
            return response()->json(['error'=>$e], 500);
        }
    }

    public function getRoleByID() {
        $roleId = Input::get('roleId') != null ? Input::get('roleId') : 1;
        $role = Role::where('id', $roleId)->first();
        $datas = [];
        if ($role != null) {
            $permission_ids = DB::table('role_has_permissions')->where('role_id', $roleId)->get();
            if ($permission_ids != null) {
                $role->permission_ids = [];
                foreach ($permission_ids as $permission_id) {
                    array_push($datas, $permission_id->permission_id);
                }
                $role->permission_ids = $datas;
            } else {
                $role->permission_ids = [];
            }
            return response()->json(['roledata' => $role], 200);
        } else {
            return response()->json(['error' => 'No user with this id: '.$roleId], 404);
        }
    }

    public function updateRole(Request $request) {
        try {
//            if ($user = JWTAuth::toUser(JWTAuth::parseToken())) {
            $cur_role = Role::findById($request['id']);
            if ($request['role_name'] != $cur_role->name) {
                DB::table('roles')->where('id', $request['id'])->update(['name' => $request['name']]);
            }
            $role = Role::findById($request['id']);
            DB::table('role_has_permissions')->where('role_id', $request['id'])->delete();
            $permission_ids = ($request['permission_ids']);
            foreach ($permission_ids as $permission_id) {
                $permission = Permission::findById($permission_id);
                $role->givePermissionTo($permission);
                $permission->assignRole($role);
            }
            return response()->json(['result' => 'Successfully Updated Role with Permissions'], 200);
//            } else {
//                return response()->json(['error' => 'User not Found'], 404);
//            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'Failed Update Role'], 500);
        }
    }

    public function fileUpload($id, Request $request) {
        $request->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = 'assets/profiles';
            $image->move($destinationPath, $name);
            DB::table('users')->where('id',$id)->update(array(
                'avatar_path'=>$destinationPath.'/'.$name,
            ));
            $user = User::find($id);
            return response()->json(['success' => true, 'user'=>$user], 200);
        } else {
            return response()->json(['error' => "Image Upload error"], 500);
        }
    }


}
