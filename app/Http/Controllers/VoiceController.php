<?php

namespace App\Http\Controllers;

use App\Services\TextToSpeech;
use Illuminate\Http\Request;

class VoiceController extends Controller
{
    public function getVoice(Request $request){
        $text = $request->input('text');
        $clients = $request->input('clients');
        $voices = [];
        $textToSpeech = new TextToSpeech($text, 'de');

        foreach($clients as $client){
            $voices[$client] = \Storage::url($textToSpeech->getVoice($client));
        }

        return response()->json(['voices' => $voices]);
    }
}
