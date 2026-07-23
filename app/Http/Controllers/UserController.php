<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $id) {
        $user = User::findOrFail($id);

        return response()->json([
            $user,
        ]);
    }

    public function me(Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or token invalid'
            ], 401);
        }

        return response()->json([
            'messages' => 'Profil pengguna berhasil diambil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d'),
            ]
        ], 200);

    }

}
