<?php

namespace App\Services;

class TextToSpeech
{
    protected $text;
    protected $lang;

    public function __construct($text, $lang)
    {
        $this->text = $text;
        $this->lang = $lang;
    }

    public function getVoice($client, $text = null, $lang = null)
    {
        if ($text) $this->text = $text;
        if ($lang) $this->lang = $lang;

        $fileName = $this->FileName();
        $disk = \Storage::disk('public');
        $extension = $this->getExtension();
        $folder = substr($fileName, 0, 2);
        $filePath = "voices/$folder/$fileName._$client.$extension";
        if (!$disk->exists($filePath)) {
            $voice = $this->$client();
            if (!$voice) return false;
            $disk->put($filePath, $voice);
        }
        return $filePath;
    }

    protected function google()
    {
        $url = 'https://translate.google.com/translate_tts?client=tw-ob&ie=UTF-8&tl=' . $this->lang . '&q=' . urlencode($this->text);
        return file_get_contents($url);
    }

    protected function collins()
    {
        $url = 'https://www.collinsdictionary.com/dictionary/german-english/' . urlencode($this->text);
        $client = new \GuzzleHttp\Client(['allow_redirects' => ['track_redirects' => true]]);
        $res = $client->request('GET', $url);
        if ($res->getStatusCode() !== 200) {
            return false;
        }
        $titleRegex = "/<title>[\w\s]*&ldquo;(\w*)&rdquo;[\w\s|-]*<\/title>/";
        $str = $res->getBody()->getContents();
        $matchs = [];
        preg_match_All($titleRegex, $str, $matchs);
        if (count($matchs) === 0 || count($matchs[1]) === 0) {
            return false;
        }
        $word = strtolower($matchs[1][0]);
        $parser = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $parser->loadHTML($str);
        libxml_use_internal_errors($internalErrors);
        $xpath = new \DOMXPath($parser);
        $nodes = $xpath->query("//*[@id='{$word}__1']/div[2]/span[1]/span[1]/a[1]");
        if ($nodes->count() === 0) {
            return false;
        }
        $audioLink = $nodes[0]->getAttribute('data-src-mp3');
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $audioLink);
        if ($res->getStatusCode() !== 200) {
            return false;
        }
        return $res->getBody()->getContents();
    }

    private function FileName()
    {
        return md5($this->text);
    }

    private function getExtension()
    {
        return 'mp3';
    }
}
