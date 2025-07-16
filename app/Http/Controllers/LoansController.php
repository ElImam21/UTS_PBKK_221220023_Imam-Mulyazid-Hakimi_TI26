<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoansController extends Controller
{
    protected $allowedEmails = ['admin@gmail.com', 'manager@gmail.com'];

    private function isAdminOrManager()
    {
        return in_array(Auth::user()->email, $this->allowedEmails);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($this->isAdminOrManager()) {
            $query = Loan::with(['user', 'book']);

            // Pencarian berdasarkan nama user atau judul buku
            if ($request->has('name')) {
                $searchTerm = $request->name;
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                })->orWhereHas('book', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
                });
            }

            $loans = $query->get();

            return response()->json($loans);
        }

        return response()->json(
            Loan::with(['user', 'book'])
                ->where('user_id', $user->user_id)
                ->get()
        );
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak: hanya admin dan manager yang diizinkan'], 403);
        }

        $loan = Loan::with(['user', 'book'])
            ->where('loans_id', $id)
            ->orWhere('user_id', $id)
            ->first();

        if (!$loan) {
            return response()->json(['message' => 'Loan tidak ditemukan'], 404);
        }

        return response()->json($loan);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'user_id' => 'required|string',
            'book_id' => 'required|string',
        ]);

        // Pastikan user_id dan book_id benar-benar ada
        if (!User::where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User_id tidak ditemukan'], 404);
        }

        if (!Book::where('book_id', $validated['book_id'])->exists()) {
            return response()->json(['message' => 'Book_id tidak ditemukan'], 404);
        }

        // Admin bisa buat pinjaman untuk siapa pun
        if ($this->isAdminOrManager()) {
            $loan = Loan::create([
                'loans_id' => (string) Str::ulid(),
                'user_id' => $validated['user_id'],
                'book_id' => $validated['book_id'],
            ]);
            return response()->json(['message' => 'Loan berhasil dibuat', 'data' => $loan], 201);
        }

        // Customer hanya bisa membuat pinjaman untuk dirinya sendiri
        if ($user->user_id !== $validated['user_id']) {
            return response()->json(['message' => 'Akses ditolak: tidak bisa meminjam untuk user lain'], 403);
        }

        $loan = Loan::create([
            'loans_id' => (string) Str::ulid(),
            'user_id' => $validated['user_id'],
            'book_id' => $validated['book_id'],
        ]);

        return response()->json(['message' => 'Loan berhasil dibuat', 'data' => $loan], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $loan = Loan::find($id);
        if (!$loan) {
            return response()->json(['message' => 'Loan tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|string',
            'book_id' => 'required|string',
            'return_date' => 'nullable|date'
        ]);

        $user = User::where('user_id', $validated['user_id'])->first();
        $book = Book::where('book_id', $validated['book_id'])->first();

        if (!$user || !$book) {
            return response()->json(['message' => 'User atau Book tidak ditemukan'], 404);
        }

        $loan->update([
            'user_id' => $user->user_id,
            'book_id' => $book->book_id,
            'return_date' => $validated['return_date'] ?? null
        ]);

        return response()->json([
            'message' => 'Update berhasil',
            'data' => $loan->fresh(['user', 'book'])
        ]);
    }

    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->delete();

        return response()->json(['message' => 'Loan deleted successfully']);
    }

}
