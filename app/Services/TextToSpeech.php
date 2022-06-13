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
        $html = $res->getBody()->getContents();
        $parser = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $parser->loadHTML($html);
        libxml_use_internal_errors($internalErrors);
        $xpath = new \DOMXPath($parser);
        $meta = $xpath->query('/html/head/meta[@property="og:url"]');
        if (!$meta->length) return false;
        $word = array_reverse(explode('/',$meta[0]->getAttribute('content')))[0];
        $nodes = $xpath->query('//*[@id="'.$word.'__1"]/div[@class="mini_h2"]/span[@class="form pron"]/span[@class="ptr hwd_sound type-hwd_sound"]/a[@data-src-mp3]');
        if (!$nodes->length) return false;
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
