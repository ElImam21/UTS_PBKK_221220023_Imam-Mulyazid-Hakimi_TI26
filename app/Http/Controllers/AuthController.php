<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Untuk ULID
use App\Models\User;
use App\Models\UserDetail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            // Tambahkan pengecekan khusus untuk email sudah terdaftar
            if ($validator->errors()->has('email')) {
                return response()->json([
                    'message' => 'Email sudah digunakan'
                ], 422);
            }

            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate ULID sekali saja
        $ulid = Str::ulid();

        // Buat user utama
        $user = User::create([
            'user_id' => $ulid,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'membership_date' => now()->toDateString(),
        ]);

        // Otomatis buat record di users_detail
        UserDetail::create([
            'user_id' => $ulid,
            // Kolom lainnya bisa diisi default value atau dibiarkan null
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token // Kirim token ke frontend
        ]);
    }
    // ✅ Fungsi Logout: Hapus token yang sedang aktif
    public function logout(Request $request)
    {
        // Hapus semua token user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil, semua token dihapus.'
        ]);
    }
}
