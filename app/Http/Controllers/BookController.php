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
        $books = Book::all();
        $bookDirectories = \Storage::disk('public')->directories('books');
        foreach ($bookDirectories as $bookDirectory) {
            $directoryName = \Str::afterLast($bookDirectory, '/');
            $book = $books->first(function ($book) use ($directoryName) {
                return $book->folderName === $directoryName;
            });
            if (!$book) {
                $book = new Book();
                $book->title = $directoryName;
                $book->save();
                \Storage::disk('public')->move($bookDirectory, $book->path);
                $books->push($book);
            }
        }
        $books = $books->sortByDesc('created_at');
        return Inertia::render('Books/Index')->with([
            'books' => BookResource::collection($books),
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
