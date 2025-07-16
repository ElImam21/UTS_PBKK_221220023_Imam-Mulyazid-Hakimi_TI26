<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorsController extends Controller
{
    private function isAdminOrManager()
    {
        $user = Auth::user();
        return in_array($user->email, ['admin@gmail.com', 'manager@gmail.com']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Akses ditolak'], 401);
        }

        // Jika ada parameter name, filter berdasarkan nama
        if ($request->has('name')) {
            $name = $request->query('name');
            $authors = Author::where('name', 'LIKE', '%' . $name . '%')->get();
            return response()->json($authors, 200);
        }

        // Siapa pun yang login boleh melihat semua data
        return response()->json(Author::all(), 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Akses ditolak. Harus login terlebih dahulu.'], 401);
        }

        // Hanya admin/manager yang boleh akses data berdasarkan ID
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak. Hanya admin atau manager yang bisa mengakses data berdasarkan ID.'], 403);
        }

        $author = Author::find($id);
        if (!$author) {
            return response()->json(['message' => 'Author tidak ditemukan.'], 404);
        }

        return response()->json($author, 200);
    }

    public function store(Request $request)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Validasi data input
        $request->validate([
            'name' => 'required|string',
            'nationality' => 'required|string',
            'birthdate' => 'required|string',
        ]);

        // Simpan data ke tabel authors
        $author = Author::create($request->all());

        // Response JSON sukses
        return response()->json([
            'message' => 'Author berhasil ditambahkan.',
            'data' => $author
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $author = Author::findOrFail($id);

        // Validasi input
        $request->validate([
            'name' => 'required|string',
            'nationality' => 'required|string',
            'birthdate' => 'required|string',
        ]);

        $author->update($request->all());

        return response()->json([
            'message' => 'Update berhasil.',
            'data' => $author
        ]);
    }
    public function destroy($id)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        Author::destroy($id);

        return response()->json([
            'message' => 'Delete authors berhasil.'
        ]);
    }
}
