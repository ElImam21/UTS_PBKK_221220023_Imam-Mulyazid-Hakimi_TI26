<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BooksController extends Controller
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

        $isAdminOrManager = $this->isAdminOrManager();

        // Jika ada parameter book_id
        if ($request->has('book_id')) {
            if ($isAdminOrManager) {
                $book = Book::where('book_id', $request->book_id)->first();
                return $book ? response()->json($book) : response()->json(['message' => 'Book not found'], 404);
            } else {
                return response()->json(['message' => 'Akses di tolak'], 403);
            }
        }

        // Jika ada parameter title
        if ($request->has('title')) {
            $books = Book::where('title', 'like', '%' . $request->title . '%')->get();
            return response()->json($books);
        }

        // Tanpa parameter apapun
        return response()->json(Book::all());
    }

    public function store(Request $request)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'isbn' => 'required|string',
            'publisher' => 'required|string',
            'year_publised' => 'required|string',
            'stock' => 'required|integer',
        ]);

        $validated['book_id'] = (string) Str::ulid();

        try {
            $book = Book::create($validated);
            return response()->json($book, 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan buku: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan buku'], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($this->isAdminOrManager()) {
            $book = Book::where('book_id', $id)->first();
            return $book ? response()->json($book) : response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json(['message' => 'Akses di tolak'], 403);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses di tolak'], 403);
        }

        $book = Book::where('book_id', $id)->first();

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'isbn' => 'sometimes|required|string',
            'publisher' => 'sometimes|required|string',
            'year_publised' => 'sometimes|required|string',
            'stock' => 'sometimes|required|integer',
        ]);

        $book->update($validated);

        return response()->json([
            'message' => 'Update berhasil',
            'data' => $book
        ]);
    }

    public function destroy($id)
    {
        if (!$this->isAdminOrManager()) {
            return response()->json(['message' => 'Akses di tolak'], 403);
        }

        $book = Book::where('book_id', $id)->first();

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }
}
