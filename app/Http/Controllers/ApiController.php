<?php

namespace App\Http\Controllers;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['message' => 'Something went wrong.', 'error_message' => $this->validationMessage($validator->errors()->toArray())]], 422);
        }
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['message' => 'Something went wrong.', 'error_message' => $this->validationMessage($validator->errors()->toArray())]], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function preference(Request $request)
    {
        $user_id = Auth::user()->id;
        $preferecne = Preference::updateOrCreate(
            ['user_id' => $user_id],
            [
                'sources' => $request->sources ?? "",
                'category' => $request->category ?? "",
                'authors' => $request->authors ?? ""
            ]
        );

        return response()->json([
            'status' => 'success',
            'preferecne' => $preferecne
        ]);
    }

    public function getPreference()
    {
        $user_id = Auth::user()->id;
        $preferecne = Preference::where('user_id', $user_id)->first();

        return response()->json([
            'status' => 'success',
            'preferecne' => $preferecne
        ]);
    }
}
