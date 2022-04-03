@extends('layout')
@section('title', "$phrase->phrase")
@section('content')
    <form class="container min-h-screen p-2 space-y-2" method="post" action="{{ route('phrases.update', $phrase) }}">
        @csrf
        @method('PUT')
        <div class="flex flex-col justify-items-stretch gap-2">
            <div class="form-control">
                <label for="text" class="label">
                    <label class="label-text" for="text">Text:</label>
                    <label class="label-text-alt">
                        <button type="button" class="btn btn-xs glass text-gray-700" id="translate-text" onclick="translateText()">
                            <i class="fas fa-globe-americas"></i>
                        </button>
                    </label>
                </label>
                <textarea name="text" id="text" class="textarea" rows="1">{{ old('text', $phrase->phrase) }}</textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning">Meaning Persian:</label>
                <textarea name="meaning[fa]" id="meaning-fa" class="textarea" rows="1">{{ old('meaning-fa', $meaning['fa']) }}</textarea>
            </div>
            <div class="form-control">
                <label class="label-text" for="meaning">Meaning English:</label>
                <textarea name="meaning[en]" id="meaning-en" class="textarea" rows="1">{{ old('meaning-en', $meaning['en']) }}</textarea>
            </div>
        </div>
        <div class="w-full btn-group">
            <button type="button" class="btn btn-primary" onclick="openTranslateText('google','en')">Google: EN</button>
            <button type="button" class="btn btn-primary" onclick="openTranslateText('google','fa')">Google: FA</button>
        </div>
        <div class="w-full btn-group">
            <button type="button" class="btn btn-success" onclick="addOption()">Add Option</button>
            <button type="button" class="btn btn-info" onclick="addExample()">Add Example</button>
            <button type="button" class="btn btn-warning" onclick="addVoice()">Add Voice</button>
        </div>
        <div class="flex flex-col items-stretch gap-1 bg-gray-400 rounded-lg p-3" id="informations">
            @foreach($options as $option => $value)
                <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700">
                    <div class="input-group flex-grow">
                        <select class="select w-1/4" name="options-name[]" required>
                            <option value="type" @selected($option === 'type')>Type</option>
                            <option value="gender" @selected($option === 'gender')>Gender</option>
                            <option value="plural" @selected($option === 'plural')>Plural</option>
                            <option value="past" @selected($option === 'past')>Past</option>
                            <option value="participle" @selected($option === 'participle')>Participle</option>
                            <option value="case" @selected($option === 'case')>Case</option>
                        </select>
                        <div class="divider divider-horizontal m-0"></div>
                        <input type="text" class="input w-full" name="options-value[]" placeholder="Option Value" required value="{{ $value }}">
                    </div>
                    <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            @endforeach
            @foreach($examples as $example => $meaning)
                <div class="flex gap-2 pb-2 items-center border-b-2 border-b-gray-700">
                    <div class="flex-grow">
                        <input type="text" class="input w-full" name="examples-text[]" placeholder="Example" required value="{{ $example }}">
                        <div class="divider m-0"></div>
                        <input type="text" class="input w-full" name="examples-meaning[]" placeholder="Meaning" required value="{{ $meaning }}">
                    </div>
                    <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            @endforeach
            @foreach($voices as $voice => $link)
                <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700">
                    <div class="input-group flex-grow">
                        <select class="select w-1/4" name="voices-name[]" required>
                            <option value="google" @selected($voice === 'google')>Google</option>
                            <option value="duden" @selected($voice === 'duden')>Duden</option>
                            <option value="collins" @selected($voice === 'collins')>Collins</option>
                        </select>
                        <div class="divider divider-horizontal m-0"></div>
                        <input type="text" class="input w-full" name="voices-link[]" placeholder="Voice Link" required value="{{ $link }}">
                    </div>
                    <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-outline btn-wide gap-2 mx-auto">
            Save
            <i class="fas fa-save"></i>
        </button>
    </form>
    <template id="option-template">
        <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700">
            <div class="input-group flex-grow">
                <select class="select w-1/4" name="options-name[]" required>
                    <option value="type">Type</option>
                    <option value="gender">Gender</option>
                    <option value="plural">Plural</option>
                    <option value="past">Past</option>
                    <option value="participle">Participle</option>
                    <option value="case">Case</option>
                </select>
                <div class="divider divider-horizontal m-0"></div>
                <input type="text" class="input w-full" name="options-value[]" placeholder="Option Value" required>
            </div>
            <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </template>
    <template id="voice-template">
        <div class="flex gap-2 pb-2 border-b-2 border-b-gray-700">
            <div class="input-group flex-grow">
                <select class="select w-1/4" name="voices-name[]" required>
                    <option value="google">Google</option>
                    <option value="duden">Duden</option>
                    <option value="collins">Collins</option>
                </select>
                <div class="divider divider-horizontal m-0"></div>
                <input type="text" class="input w-full" name="voices-link[]" placeholder="Voice Link" required>
            </div>
            <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </template>
    <template id="example-template">
        <div class="flex gap-2 pb-2 items-center border-b-2 border-b-gray-700">
            <div class="flex-grow">
                <input type="text" class="input w-full" name="examples-text[]" placeholder="Example" required>
                <div class="divider m-0"></div>
                <input type="text" class="input w-full" name="examples-meaning[]" placeholder="Meaning" required>
            </div>
            <button type="button" class="btn btn-outline btn-error" onclick="(e=> $(e.target).closest('button').parent().remove())(event)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </template>
@endsection
@push('scripts')
    <script>
        function openTranslateText(website, language) {
            switch (website) {
                case 'google':
                    let link = 'https://translate.google.com/#view=home&op=translate&sl=auto&tl=' + language + '&text=' + $('#text').val()
                    window.open(link, '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes,left=500,top=0')
                    break
            }
        }

        function addOption() {
            let template = document.getElementById('option-template').content.cloneNode(true)
            document.getElementById('informations').appendChild(template)
        }

        function addExample() {
            let template = document.getElementById('example-template').content.cloneNode(true)
            document.getElementById('informations').appendChild(template)
        }

        function addVoice() {
            let template = document.getElementById('voice-template').content.cloneNode(true)
            document.getElementById('informations').appendChild(template)
        }

        function translateText(){
            let text = $('#text').val()
            let translateUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&dt=t&dt=bd&dj=1&q=${text}&tl=%lang%`
            fetch(translateUrl.replace('%lang%', 'fa'))
                .then(res => res.json())
                .then(res => {
                    $('#meaning-fa').val(res.sentences[0].trans)
                })
            fetch(translateUrl.replace('%lang%', 'en'))
                .then(res => res.json())
                .then(res => {
                    $('#meaning-en').val(res.sentences[0].trans)
                })
        }

        document.addEventListener('DOMContentLoaded', function () {
            (function ($) {
            })(window.jQuery)
        })

    </script>
@endpush
