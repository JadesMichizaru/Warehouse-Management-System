<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|password|min:8'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $tokens = $request->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User Successfully Registered!',
            'data' => $users,
            'token' => $tokens,
        ]);
    }


    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|password|min:8'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        // Get credentials from request
         $credentials = [
             'email' => $request->email,
             'password' => $request->password,
         ];

         $user = Auth::user();

        $tokens = $request->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User Successfully Login',
            'data' => $user,
            'token' => $tokens,
        ]);
    }
}
