<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\PageResource;
use App\Models\Book;
use App\Models\Page;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Stichoza\GoogleTranslate\GoogleTranslate;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PageController extends Controller
{
    public function index(Book $book)
    {
        if ($book->pages->count() === 0) {
            if (\Storage::disk('public')->exists($book->path .'/pages')) {
                $pages = \Storage::disk('public')->files($book->path .'/pages');

                foreach ($pages as $page) {
                    $image = Str::afterLast($page, '/');
                    $imageName = Str::beforeLast($image, '.');
                    $imageExtension = Str::afterLast($image, '.');
                    $imageName = \Arr::last(preg_split('/[^\d]/', $imageName));
                    $book->pages()->create([
                       'number' => $imageName
                    ]);
                    \Storage::disk('public')->move($page, $book->path .'/pages/' .$imageName .'.' .$imageExtension);
                }

                if (count($pages) > 0) {
                    $book->refresh();
                }
            }
        }

        $pages = $book->pages->sortBy('number');
        $book->unsetRelation('pages');
        $book->setRelation('pages', $pages);

        return Inertia::render('Pages/Index')->with([
            'book' => BookResource::make($book),
            'users' => User::whereNot('email', 'admin@admin.com')->with(['permissions' => function ($query) {
                $query->select('name');
            }])->get()
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

    public function show(Book $book, $page)
    {
        $page = $book->pages()->where('number', $page)->firstOrFail();
        return Inertia::render('Pages/Show')->with([
            'book' => BookResource::make($book),
            'next' => $page->next?PageResource::make($page->next):null,
            'previous' => $page->previous?PageResource::make($page->previous):null,
            'page' => PageResource::make($page),
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
            $imagePath = $request->image->path();
            $tesseract = new TesseractOCR($imagePath);
            $tesseract->lang(env('TESSERACT_LANG'));
            try {
                $text = $tesseract->run();
            }catch (\Exception $exception){
                $text = '';
            }
        }
        $highlights = json_decode($validated['highlights'] ?: '[]',true);
        $oldHighlights = collect($page->highlights);
        $highlights = array_map(function ($highlight) use ($oldHighlights) {
            $oldHighlight = $oldHighlights
                ->where('position', $highlight['position'])
                ->where('size', $highlight['size'])
                ->first();

            if ($oldHighlight && isset($oldHighlight['data']['phrase_id'])) {
                $highlight['data']['phrase_id'] = $oldHighlight['data']['phrase_id'];
            }
            return $highlight;
        }, $highlights);
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
            'text' => ['nullable', 'string'],
            'languages' => ['required', 'array'],
            'languages.fa' => ['required', 'boolean'],
            'languages.en' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return abort(422, $validator->errors()->first());
        }
        $validated = $validator->validated();

        return Inertia::render('Phrases/Create')->with([
            'book' => BookResource::make($book),
            'page' => PageResource::make($page),
            'text' => $validated['text'],
            'languages' => $validated['languages'],
            'highlights' => $validated['highlights'],
            'translated' => [
                'fa' => '' ?? GoogleTranslate::trans($validated['text'], 'fa'),
                'en' => '' ?? GoogleTranslate::trans($validated['text'], 'en')
            ]
        ]);
    }

    public function setStatus($book, Page $page, Request $request){
        $request->validate([
           'status' => ['required', Rule::in([
               Page::STATUS_INITIAL,
               Page::STATUS_PENDING,
               Page::STATUS_DONE,
               Page::STATUS_APPROVED
           ])]
        ]);

        $page->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'status' => $page->status
        ]);
    }
}
