<?php

namespace App\Http\Controllers;

use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Models\Phrase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhraseController extends Controller
{
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'text' => 'required|string',
            'meaning' => 'required|array',
            'highlights' => 'required|json',
            'page_id' => ['required','integer','exists:pages,id'],
            'options' => 'array',
            'options.*.name' => 'required|string',
            'options.*.value' => 'required|string',
            'examples' => 'array',
            'examples.*.text' => 'required|string',
            'examples.*.meaning' => 'required|array',
            'examples.*.meaning.*' => 'required|string',
            'voices' => 'array',
            'voices.*.name' => 'required|string',
            'voices.*.link' => 'required|string',
            'exercise' => ['nullable'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $page = Page::find($request->page_id);
        $phrase = new Phrase();
        $phrase->phrase = $request->text;

        $options = $request['options'] ?: [];
        $examples = $request['examples'] ?: [];
        $voices = $request['voices'] ?: [];

        $phrase->information = [
            'meaning' => $request->meaning,
            'options' => $options,
            'examples' => $examples,
            'voices' => $voices,
            'exercise' => $request->exercise,
        ];
        $phrase->page_id = $page->id;
        $phrase->book_id = $page->book_id;
        $phrase->save();

        $phraseHighlights = json_decode($request->highlights, true);
        $page->highlights = array_map(function ($highlight) use ($phrase,$phraseHighlights) {
            foreach ($phraseHighlights as $phraseHighlight) {
                $isEqualPositionX = $highlight['position']['x'] === $phraseHighlight['position']['x'];
                $isEqualPositionY = $highlight['position']['y'] === $phraseHighlight['position']['y'];
                $isEqualPosition = $isEqualPositionX && $isEqualPositionY;
                $isEqualWidth = $highlight['size']['width'] === $phraseHighlight['size']['width'];
                $isEqualHeight = $highlight['size']['height'] === $phraseHighlight['size']['height'];
                $isEqualSize = $isEqualWidth && $isEqualHeight;
                $isEqual = $isEqualPosition && $isEqualSize;
                if ($isEqual) {
                    if(!isset($highlight['data'])) {
                        $highlight['data'] = [];
                    }
                    $highlight['data']['phrase_id'] = $phrase->id;
                }
            }
            return $highlight;
        }, $page->highlights);
        $page->save();

        return redirect()->route('phrases.edit', $phrase);
    }

    public function update(Request $request, Phrase $phrase){
        $request->validate([
            'text' => 'required|string',
            'meaning' => 'required|array',
            'options' => 'array',
            'options.*.name' => 'required|string',
            'options.*.value' => 'required|string',
            'examples' => 'array',
            'examples.*.text' => 'required|string',
            'examples.*.meaning' => 'required|array',
            'examples.*.meaning.*' => 'required|string',
            'voices' => 'array',
            'voices.*.name' => 'required|string',
            'voices.*.link' => 'required|string',
            'exercise' => ['nullable'],
        ]);

        $phrase->phrase = $request->text;

        $phrase->information = [
            'meaning' => $request->meaning,
            'options' => $request->options,
            'examples' => $request->examples,
            'voices' => $request->voices,
            'exercise' => $request->exercise,
        ];
        $phrase->save();
        return back()->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Phrase updated successfully',
            ],
        ]);
    }

    public function edit(Phrase $phrase){
        return Inertia::render('Phrases/Edit')->with([
            'phrase' => $phrase,
        ]);
    }

    public function extract(Request $request){
        $request->validate([
            'pages' => 'required|array',
        ]);

        $pages = Page::whereIn('id', $request->pages)->get();

        $phrases = new Collection();

        foreach ($pages as $page) {
            $phrases=  $phrases->merge($page->phrases);
        }
        return Inertia::render('Phrases/Extract')->with([
            'phrases' => $phrases,
            'pages' => PageResource::collection($pages),
        ]);
    }

    public function destroy(Phrase $phrase){
        $phrase->delete();
        return back()->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Phrase deleted successfully',
            ],
        ]);
    }
}
