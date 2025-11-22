<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function saveBase64Image($base64)
    {
        if (!$base64) return null;

        $folder = "uploads/profile_images";
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $m)) {
            $ext = $m[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $ext = "png";
        }

        $data = base64_decode($base64);
        if (!$data) return null;

        $filename = "$folder/" . uniqid("profile_", true) . ".$ext";
        file_put_contents(public_path($filename), $data);

        return $filename;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|string|min:6',
            'profile_image' => 'nullable|string',
        ]);

        $profileImage = $this->saveBase64Image($request->profile_image);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'profile_image' => $profileImage,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        UserAction::create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'User registered',
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->orWhere('phone_number', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        UserAction::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'User logged in',
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();

            UserAction::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'User logged out',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }
}
