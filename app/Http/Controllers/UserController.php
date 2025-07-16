<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Tampilkan semua user (hanya admin dan manager)
    public function index()
    {
        $user = Auth::user();

        // Jika admin atau manager, tampilkan semua user
        if ($user->email === 'admin@gmail.com' || $user->email === 'manager@gmail.com') {
            return response()->json(User::all(), 200);
        }

        // Jika bukan admin/manager (customer), tampilkan hanya data diri sendiri
        return response()->json([$user], 200); // bungkus dalam array agar bentuk respons tetap konsisten (array of users)
    }


    // Tampilkan user berdasarkan ID
    public function show($id)
    {
        $user = Auth::user();

        // Jika admin atau manager, izinkan akses user manapun berdasarkan ID
        if ($user->email === 'admin@gmail.com' || $user->email === 'manager@gmail.com') {
            return response()->json(User::findOrFail($id), 200);
        }

        // Jika user biasa, hanya boleh akses data dirinya sendiri
        if ($user->user_id === $id) {
            return response()->json($user, 200);
        }

        // Selain itu, tolak akses
        return response()->json(['message' => 'Akses ditolak'], 403);
        
    }

    public function update(Request $request, $user_id)
    {
        // Validasi data yang dikirim
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'membership_date' => 'sometimes|date'
        ]);

        // Temukan user berdasarkan kolom user_id
        $user = User::where('user_id', $user_id)->firstOrFail();

        // Update hanya kolom yang dikirim
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('membership_date')) {
            $user->membership_date = $request->membership_date;
        }

        $user->save();

        return response()->json([
            'message' => 'Pengguna berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function destroy($user_id)
    {
        $user = User::where('user_id', $user_id)->firstOrFail();

        $user->delete();

        return response()->json([
            'message' => 'Pengguna berhasil dihapus'
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Registrasi user tidak diizinkan di sini'], 403);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = Auth::user();

        // Periksa apakah password lama cocok
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Password lama tidak cocok'], 401);
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string',
        ]);

        $user = Auth::user();

        // Validasi akses
        if (!in_array($user->email, ['admin@gmail.com', 'manager@gmail.com'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $name = $request->query('name');
        $query = User::query();

        if ($name) {
            // Fokus hanya pada pencarian NAMA (bukan email)
            $query->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
            });
        }

        $users = $query->get()->map(function ($user) {
            return [
                'user_id' => $user->user_id,
                'type' => 'user',
                'name' => $user->name,
                'email' => $user->email,
                'membershipDate' => $user->membership_date
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
