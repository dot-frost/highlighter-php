<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class PageController extends Controller
{
    public function index(Book $book)
    {
        if ($book->pages->count() === 0) {
            if (\Storage::disk('public')->exists($book->path .'/pages')) {
                $pages = \Storage::disk('public')->files($book->path .'/pages');
                foreach ($pages as $page) {
                    $book->pages()->create([
                       'number' => (int)Str::beforeLast($page, '.'),
                    ]);
                }
            }
        }
        return view('pages.index')->with([
            'book' => $book,
        ]);
    }

    public function store(Request $request, Book $book)
    {
        $request->validate([
            'number' => ['required'],
            'image' => ['required', 'image'],
        ]);

        $page = $book->pages()->create([
            'number' => $request->number,
        ]);

        $page->storeImage($request->image);

        return redirect()->route('books.pages.index', $book)->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Page created successfully.',
            ],
        ]);
    }

    public function show(Book $book, Page $page)
    {
        return view('pages.show')->with([
            'book' => $book,
            'page' => $page,
        ]);
    }

    public function destroy(Book $book, Page $page)
    {
        $page->delete();

        return redirect()->route('books.pages.index', $page->book)->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Page deleted successfully.',
            ],
        ]);
    }

    public function update(Request $request, Book $book, Page $page)
    {
        $validated = $request->validate([
            'highlights' => ['nullable', 'json'],
            'image' => ['nullable','image']
        ]);
        if ($request->hasFile('image')) {
            exec("tesseract {$request->image->path()} /tmp/output -l eng");
            $text = Str::of(file_get_contents('/tmp/output.txt'))->replace(["\n", "\f"], '')->trim()->value();
        }
        $highlights = json_decode($validated['highlights'] ?: '[]',true);
        $page->update([
            'highlights' => $highlights
        ]);

        return response()->json([
            'success' => true,
            'text' => $text ?? Null,
            'highlights' => $highlights,
        ]);
    }

    /**
     * @throws \ErrorException
     */
    public function lastText(Request $request, Book $book, Page $page)
    {
        $validator = validator($request->all(), [
            'highlights' => ['required', 'json'],
            'text' => ['required', 'string'],
            'languages' => ['required', 'array'],
            'languages.fa' => ['required', 'boolean'],
            'languages.en' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return abort(422, $validator->errors()->first());
        }
        $validated = $validator->validated();

        return view('pages.last-text')->with([
            'book' => $book,
            'page' => $page,
            'text' => $validated['text'],
            'languages' => $validated['languages'],
            'highlights' => $validated['highlights'],
            'translated' => [
                'fa' => '' ?? GoogleTranslate::trans($validated['text'], 'fa'),
                'en' => '' ?? GoogleTranslate::trans($validated['text'], 'en')
            ]
        ]);
    }
}
