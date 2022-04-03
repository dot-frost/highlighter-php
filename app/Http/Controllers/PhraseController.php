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
            'exercise' => ['nullable'],
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

        return redirect()->route('phrases.edit', $phrase)->with([
            'alert' => [
                'type' => 'success',
                'message' => 'Phrase has been created.',
            ],
        ]);
    }

    public function update(Request $request, Phrase $phrase){
        $request->validate([
            'text' => 'required|string',
            'meaning' => 'required|array',
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
        $text = $phrase->phrase;
        $meaning = $phrase->information['meaning'];
        $options = $phrase->information['options'];
        $optionsName = old('options-name', array_keys($options));
        $optionsValue = old('options-value', array_values($options));
        $options = array_combine($optionsName, $optionsValue);
        $examples = $phrase->information['examples'];
        $examplesText = old('examples-text', array_keys($examples));
        $examplesMeaning = old('examples-meaning', array_values($examples));
        $examples = array_combine($examplesText, $examplesMeaning);
        $voices = $phrase->information['voices'];
        $voicesName = old('voices-name', array_keys($voices));
        $voicesLink = old('voices-link', array_values($voices));
        $voices = array_combine($voicesName, $voicesLink);
        return view('phrases.edit')->with([
            'phrase' => $phrase,
            'text' => $text,
            'meaning' => $meaning,
            'options' => $options,
            'examples' => $examples,
            'voices' => $voices,
            'exercise' => $phrase->information['exercise'] ?? null,
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
        return view('phrases.extract')->with([
            'phrases' => $phrases,
            'pages' => $pages,
        ]);
    }
}
