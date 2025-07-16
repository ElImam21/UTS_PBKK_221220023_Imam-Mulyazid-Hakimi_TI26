<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Validator;

class UserDetailController extends Controller
{
    /**
     * Menampilkan detail user beserta informasi tambahan
     *
     * @param  string  $user_id  ULID user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($user_id)
    {
        $currentUser = Auth::user();

        // Jika admin/manager, izinkan akses semua user
        $isAdminOrManager = in_array($currentUser->email, ['admin@gmail.com', 'manager@gmail.com']);

        // Jika user biasa, hanya boleh akses data sendiri
        if (!$isAdminOrManager && $currentUser->user_id !== $user_id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Ambil data user dengan relasi detail
        $user = User::with('detail')->findOrFail($user_id);

        // Format response
        $response = [
            'basic_info' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'additional_info' => $user->detail ? [
                'address' => $user->detail->address,
                'phone_number' => $user->detail->phone_number,
                'birth_date' => $user->detail->birth_date,
                'bio' => $user->detail->bio,
            ] : null
        ];

        return response()->json($response);
    }

    public function update(Request $request, $user_id)
    {
        $currentUser = Auth::user();

        // Validasi akses
        $isAdminOrManager = in_array($currentUser->email, ['admin@gmail.com', 'manager@gmail.com']);
        if (!$isAdminOrManager && $currentUser->user_id !== $user_id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            // Data dari tabel users
            'name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:50|unique:users,email,' . $user_id . ',user_id',

            // Data dari tabel users_detail
            'address' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'birth_date' => 'sometimes|date',
            'bio' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mulai transaction untuk memastikan konsistensi data
        \DB::transaction(function () use ($request, $user_id) {
            // Update data di tabel users
            $user = User::where('user_id', $user_id)->firstOrFail();

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            $user->save();

            // Update atau create data di tabel users_detail
            $userDetail = UserDetail::updateOrCreate(
                ['user_id' => $user_id],
                [
                    'address' => $request->address ?? null,
                    'phone_number' => $request->phone_number ?? null,
                    'birth_date' => $request->birth_date ?? null,
                    'bio' => $request->bio ?? null
                ]
            );
        });

        // Ambil data terbaru untuk response
        $updatedUser = User::with('detail')->findOrFail($user_id);

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'data' => [
                'basic_info' => [
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                ],
                'additional_info' => $updatedUser->detail ? [
                    'address' => $updatedUser->detail->address,
                    'phone_number' => $updatedUser->detail->phone_number,
                    'birth_date' => $updatedUser->detail->birth_date,
                    'bio' => $updatedUser->detail->bio,
                ] : null
            ]
        ]);
    }
}