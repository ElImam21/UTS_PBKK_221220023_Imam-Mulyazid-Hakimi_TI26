<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\BookAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookAuthorsController extends Controller
{
    public function __construct()
    {
        // Semua pengguna wajib login dan memiliki token
        $this->middleware('auth:sanctum');
    }

    private function isAdminOrManager()
    {
        $user = auth()->user();
        return $user && in_array($user->email, ['admin@gmail.com', 'manager@gmail.com']);
    }

    // ========== CREATE ==========
    public function store(Request $request)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses di tolak'], 403);
        }

        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'author_id' => 'required|exists:authors,author_id',
        ]);

        // Tambahan: validasi relasi tidak boleh duplikat
        $exists = BookAuthor::where('book_id', $validated['book_id'])
            ->where('author_id', $validated['author_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Relasi buku-penulis sudah ada'], 409);
        }

        $bookAuthor = BookAuthor::create($validated);
        $bookAuthor->load('book', 'author');

        return response()->json([
            'book_id' => $bookAuthor->book_id,
            'author_id' => $bookAuthor->author_id,
            'book' => $bookAuthor->book,
            'author' => $bookAuthor->author,
        ], 201);
    }

    // ========== INDEX (Read All) ==========
    public function index(Request $request)
    {
        $user = Auth::user();
        $searchQuery = $request->query('q');

        $bookAuthorsQuery = BookAuthor::with(['book', 'author']);

        if ($searchQuery) {
            $bookAuthorsQuery->whereHas('book', function ($query) use ($searchQuery) {
                $query->where('title', 'like', '%' . $searchQuery . '%');
            })->orWhereHas('author', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });
        }

        $bookAuthors = $bookAuthorsQuery->get();

        $formattedResults = $bookAuthors->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'bookauthor',
                'title' => $item->book->title . ' - ' . $item->author->name,
                'bookTitle' => $item->book->title,
                'authorName' => $item->author->name,
                'book_id' => $item->book->book_id,
                'author_id' => $item->author->author_id,
                'stock' => $item->book->stock,
                'nationality' => $item->author->nationality
            ];
        });

        return response()->json($bookAuthors);
    }

    // ========== SHOW (Read One by id) ==========
    public function show($id)
    {
        $user = Auth::user();

        // Hanya admin dan manager
        if (!in_array($user->email, ['admin@gmail.com', 'manager@gmail.com'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $bookAuthor = BookAuthor::with(['book', 'author'])->find($id);

        if (!$bookAuthor) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($bookAuthor);
    }

    // ========== UPDATE ==========
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'author_id' => 'required|exists:authors,author_id',
        ]);

        $bookAuthor = BookAuthor::findOrFail($id);
        $bookAuthor->book_id = $validated['book_id'];
        $bookAuthor->author_id = $validated['author_id'];
        $bookAuthor->save();

        return response()->json(['message' => 'Relasi buku-penulis berhasil diperbarui']);
    }

    // ========== DELETE ==========
    public function destroy($id)
    {
        $user = Auth::user();

        // Hanya admin dan manager
        if (!in_array($user->email, ['admin@gmail.com', 'manager@gmail.com'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $bookAuthor = BookAuthor::find($id);

        if (!$bookAuthor) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $bookAuthor->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json([], 200);
        }

        $results = BookAuthor::with(['book', 'author'])
            ->whereHas('book', function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%');
            })
            ->orWhereHas('author', function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%');
            })
            ->get();

        // Tambahkan ID relasi di sini
        $formattedResults = $results->map(function ($item) {
            return [
                'id' => $item->id, // PASTIKAN ID RELASI DITAMBAHKAN
                'book' => $item->book ? [
                    'book_id' => $item->book->book_id,
                    'title' => $item->book->title,
                    'stock' => $item->book->stock
                ] : null,
                'author' => $item->author ? [
                    'author_id' => $item->author->author_id,
                    'name' => $item->author->name,
                    'nationality' => $item->author->nationality
                ] : null
            ];
        });

        return response()->json($formattedResults);
    }
}
