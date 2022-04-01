@extends('layout')
@section('title', $book->title)
@section('content')
    <div class="container flex flex-col justify-start items-center min-h-screen py-2">
        <form method="post" action="{{ route('books.pages.store', $book->id) }}" enctype="multipart/form-data" class="flex flex-col items-stretch gap-2">
            <h2>Create Page</h2>
            @csrf
            <div class="form-control">
                <label class="input-group w-full">
                    <span>Number</span>
                    <input type="text" placeholder="Page Number" name="number"
                           @class(['input input-bordered input-sm w-full'=> true, 'input-error' => $errors->has('number')])
                           value="{{ old('number', (int)$book->pages->sortByDesc('number')->first()?->number + 1) }}">
                </label>
                @error('number')
                <label class="label">
                    <span class="label-text text-error">{{ $message }}</span>
                </label>
                @enderror
            </div>
            <div class="form-control">
                <label class="input-group w-full">
                    <span>Image</span>
                    <input type="file" @class(['input input-bordered input-sm w-full'=> true, 'input-error' => $errors->has('image')]) name="image">
                </label>
                @error('image')
                <label class="label">
                    <span class="label-text text-error">{{ $message }}</span>
                </label>
                @enderror
            </div>
            <div class="form-control">
                <button class="btn" type="submit">Submit</button>
            </div>
        </form>
        <div class="divider w-full">Pages</div>
        <div class="flex justify-end">
            <form action="{{ route('phrases.extract') }}" method="post">
                @csrf
                @error('pages')
                {{ $message }}
                @enderror
                @foreach($pages = $book->pages->sortBy('number') as $page)
                    <input hidden type="checkbox" id="page_{{ $page->id }}" name="pages[]" value="{{ $page->id }}">
                @endforeach
                <button class="btn" type="submit">Extract</button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
            @foreach($pages as $page)
                <div class="card bg-base-100 shadow-xl">
                    <div class="flex items-center">
                        <input type="checkbox" class="checkbox m-1" style="" oninput="$('#page_{{ $page->id }}').attr('checked', event.target.checked)">
                        <span class="p-3">{{ $page->number }}</span>
                    </div>
                    <figure><img class="aspect-square" src="{{ $page->imageThumbnail300Url }}"  alt="{{ $page->page_number }}"/></figure>
                    <div class="card-body">
                        <div class="card-actions justify-center flex-nowrap">
                            <a role="button" href="{{ route('books.pages.show', [$book, $page]) }}" class="btn btn-primary">Show</a>
                            <form method="post" action="{{ route('books.pages.destroy', [$book, $page]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-warning" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
