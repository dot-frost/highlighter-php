<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Phrase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PhraseController extends Controller
{
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'text' => 'required|string',
            'meaning' => 'required|array',
            'highlights' => 'required|json',
            'page_id' => ['required','integer','exists:pages,id'],
            'options-name' => ['required_with:options-value','array'],
            'options-name.*' => ['required_with:options-value.*','string'],
            'options-value' => ['required_with:options-name.*','array'],
            'options-value.*' => ['required_with:options-name.*','string'],
            'examples-text' => ['required_with:examples-meaning','array'],
            'examples-text.*' => ['required_with:examples-meaning.*','string'],
            'examples-meaning' => ['required_with:examples-text.*','array'],
            'examples-meaning.*' => ['required_with:examples-text.*','string'],
            'voices-name' => ['required_with:voices-link','array'],
            'voices-name.*' => ['required_with:voices-link.*','string'],
            'voices-link' => ['required_with:voices-name.*','array'],
            'voices-link.*' => ['required_with:voices-name.*','string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $page = Page::find($request->page_id);
        $phrase = new Phrase();
        $phrase->phrase = $request->text;

        $options = [];
        foreach ($request->get('options-name', []) as $index => $option) {
            $options[$option] = $request->get('options-value', [])[$index];
        }
        $examples = [];
        foreach ($request->get('examples-text', []) as $index => $example) {
            $examples[$example] = $request->get('examples-meaning', [])[$index];
        }
        $voices = [];
        foreach ($request->get('voices-name', []) as $index => $voice) {
            $voices[$voice] = $request->get('voices-link', [])[$index];
        }
        $phrase->information = [
            'meaning' => $request->meaning,
            'options' => $options,
            'examples' => $examples,
            'voices' => $voices,
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

        return response()->json($page->highlights);
    }

    public function edit(Phrase $phrase){
        return view('phrases.edit', compact('phrase'));
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
        return view('phrases.extract')->with([
            'phrases' => $phrases,
            'pages' => $pages,
        ]);
    }
}
