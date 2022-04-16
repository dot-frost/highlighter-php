<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    public function index()
    {
        return Inertia::render('Books/Index')->with([
            'books' => BookResource::collection(Book::all())
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'max:255'],
            'cover' => ['required', 'image'],
        ]);

        $book = Book::create([
            'title' => $request->title
        ]);

        $book->storeCover($request->cover);

        return redirect()->route('books.index')->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Book created successfully!'
            ]
        ]);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Book deleted successfully!'
            ]
        ]);
    }
}
