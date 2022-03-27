@extends('layout')
@section('content')
    <div class="min-h-screen">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 px-3 py-3 gap-3">
            @foreach($phrases as $phrase)
                <div class="col-span-1 card card-side bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">{{ $phrase->phrase }}</h2>
                        <div class="divider">Meaning</div>
                        <div class="flex flex-col items-stretch">
                            @foreach($phrase->information['meaning'] as $lang => $mean)
                                <div class="">
                                    <h2>{{ strtoupper($lang) }}:</h2>
                                    <p>
                                        {{ $mean }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <div class="divider">Options</div>
                        <div class="flex flex-wrap">
                            @foreach($phrase->information['options'] as $option => $value)
                                <div class="flex justify-start">
                                    <h2>{{ strtoupper($option) }}:</h2>
                                    <p>
                                        {{ $value }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <div class="divider">Examples</div>
                        <div class="flex flex-wrap">
                            @foreach($phrase->information['examples'] as $example => $mean)
                                <div class="flex justify-start">
                                    <h2 class="flex-grow">{{ $example }}</h2>
                                    <p>
                                        {{ $mean }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <div class="divider">Voices</div>
                        <div class="flex flex-wrap">
                            @foreach($phrase->information['voices'] as $voice => $link)
                                <div class="flex justify-start">
                                    <h2 class="flex-grow">{{ $voice }}</h2>
                                    <p>
                                        {{ $link }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
