@extends('layout')
@section('content')
    <div class="container mx-auto min-h-screen flex justify-center items-center">
        <form class="w-1/3" action="{{ route('login') }}" method="post">
            @csrf
            <h1 class="mb-6 text-center">Login</h1>
            <div class="flex flex-col gap-2 my-2">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-error shadow-lg">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Error! {{ $error }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="border-teal-500 p-8 border-t-8 bg-white mb-6 rounded-lg shadow-lg">
                <div class="mb-4">
                    <label class="font-bold text-grey-800 block mb-2">Email</label>
                    <input type="text" name="email"
                           class="block appearance-none w-full bg-white border border-grey-400 hover:border-gray-500 px-2 py-2 rounded shadow"
                           placeholder="Your Email" value="{{ old('email') }}">
                </div>
                <div class="mb-4">
                    <label class="font-bold text-grey-800 block mb-2">Password</label>
                    <input type="password" name="password"
                           class="block appearance-none w-full bg-white border border-grey-400 hover:border-gray-500 px-2 py-2 rounded shadow"
                           placeholder="Your Password">
                </div>
                <div class="flex items-center justify-start">
                    <button class="bg-teal-800 hover:bg-teal-500 text-white font-bold py-2 px-4 rounded" type="submit">
                        Login
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
