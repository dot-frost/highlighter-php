@extends('layout')
@section('title', $book->title . ' -' . $page->number)
@section('content')
    <div class="fixed z-10 top-0 left-0 right-0 bg-white border-b-4 border-b-gray-700">
        <div id="toolbar" class="w-full flex justify-center items-stretch p-2 gap-2">
            <label class="absolute left-2 top-1/2 transform -translate-y-1/2">
                Book:{{ $book->title }} Page: {{ $page->number }}
            </label>
            <div id="page-tools">
                <div class="btn-group">
                    <a role="button" class="btn btn-outline btn-secondary"
                       href="{{ route('books.pages.index' , $book) }}">
                        <i class="fas fa-book"></i>
                    </a>
                    @if ($previous = $page->previous)
                        <a role="button" class="btn btn-outline btn-primary" href="{{ route('books.pages.show', [$book,$previous]) }}">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                    @endif
                    @if ($next = $page->next)
                        <a role="button" class="btn btn-outline btn-primary" href="{{ route('books.pages.show', [$book,$next]) }}">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
            <div class="divider divider-horizontal m-0"></div>
            <div id="tools">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <button type="button" class="btn btn-outline btn-primary btn-active" id="highlight-tool">
                        <i class="fas fa-highlighter"></i>
                    </button>
                    <button type="button" class="btn btn-outline btn-success">
                        <i class="fas fa-note-sticky"></i>
                    </button>
                </div>
            </div>
            <div class="divider divider-horizontal m-0"></div>
            <div id="tool-options" class="flex justify-start items-center gap-1">
                <div class="flex items-stretch option highlight-tool">
                    <button type="button" class="btn btn-outline btn-primary" id="highlight-tool-timer">
                        <i class="fas fa-clock"></i>
                    </button>
                </div>
                <div class="flex items-stretch option highlight-tool">
                    <input type="color" value="#00f5f0" id="highlight-tool-color">
                </div>
                <div class="flex items-stretch option highlight-tool">
                    <input type="range" class="range range-xs" id="highlight-tool-opacity" min="0" max="100" value="50">
                </div>
                <div class="option highlight-tool">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-outline m-1">
                            <i class="fas fa-language"></i>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li>
                                <label class="swap swap-flip">
                                    <input type="checkbox" id="highlight-tool-lang-fa"/>
                                    <div class="swap-on">
                                        Persian
                                    </div>
                                    <div class="swap-off">
                                        <del>Persian</del>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <label class="swap swap-flip">
                                    <input type="checkbox" id="highlight-tool-lang-en"/>
                                    <div class="swap-on">
                                        English
                                    </div>
                                    <div class="swap-off">
                                        <del>English</del>
                                    </div>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="editor-container" class="mt-[4.5rem] min-h-[calc(100vh-4.5rem)]">
        <canvas id="canvas" class="w-full h-full"></canvas>
    </section>
    <img src="{{ $page->imageUrl }}" id="img" hidden>
@endsection
@push('scripts')
<script>
    window.page = {!! $page->toJson() !!};
    window.routeUpdateHighlights = "{{ route('books.pages.update', [$book,$page]) }}";
    window.routeLastText = "{{ route('books.pages.last-text', [$book,$page]) }}";
    window.highlights = {!! json_encode($page->highlights ?: []) !!};
    window.routePhrase = "{{ route('phrases.index') }}/";
</script>
@endpush
@section('scripts')

    https://www.google.com/url?q=http://translate.google.com/translate_tts?tl%3Dde%26q%3DFreut%2520mich%2520sehr.%26client%3Dtw-ob&sa=D&source=editors&ust=1648297297306728&usg=AOvVaw3Z7npJiZjzRHvktrNLmcMm03:51 PM
    NIXFORD
    https://www.google.com/search?q=refresh+icon&oq=refresh+icon&aqs=chrome..69i57j0i512l9.5704j0j7&sourceid=chrome&ie=UTF-8
    NIXFORD
    https://www.w3schools.com/tags/tryit.asp?filename=tryhtml_select

@endsection
