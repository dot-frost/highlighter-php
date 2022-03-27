@extends('layout')
@section('title', 'Books')
@section('content')
    <div class="container flex flex-col justify-start items-center min-h-screen py-2">
        <form method="post" action="{{ ''}}" enctype="multipart/form-data" class="flex flex-col items-stretch gap-2">
            <h2>Create Book</h2>
            @csrf
            <div class="form-control">
                <label class="input-group w-full">
                    <span>Title</span>
                    <input type="text" placeholder="info@site.com" @class(['input input-bordered input-sm w-full'=> true, 'input-error' => $errors->has('title')]) name="title" value="{{ old('title') }}">
                </label>
                @error('title')
                <label class="label">
                    <span class="label-text text-error">{{ $message }}</span>
                </label>
                @enderror
            </div>
            <div class="form-control">
                <label class="input-group w-full">
                    <span>Cover</span>
                    <input type="file" @class(['input input-bordered input-sm w-full'=> true, 'input-error' => $errors->has('cover')]) name="cover">
                </label>
                @error('cover')
                <label class="label">
                    <span class="label-text text-error">{{ $message }}</span>
                </label>
                @enderror
            </div>
            <div class="form-control">
                <button class="btn" type="submit">Submit</button>
            </div>
        </form>
        <div class="divider w-full">Books</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
            @foreach($books as $book)
                <div class="card bg-base-100 shadow-xl">
                    <figure><img class="aspect-square" src="{{ $book->coverUrl }}"  alt="{{ $book->title }}"/></figure>
                    <div class="card-body">
                        <h2 class="card-title">{{ $book->title }}</h2>
                        <div class="card-actions justify-center flex-nowrap">
                            <a role="button" href="{{ route('books.pages.index', $book->id) }}" class="btn btn-primary">Pages</a>
                            <form method="post" action="{{ route('books.destroy', $book->id) }}">
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
