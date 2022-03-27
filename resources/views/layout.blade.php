<!-- create layout with tailwindcss -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Fontawesome -->
    <link rel="stylesheet" href="{{ asset('css/icons.css') }}">
    <!-- Styles -->
    <link href="{{ asset('css/vendor.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100">

<main class="w-full flex flex-col items-center">
    @yield('content')
</main>

@if(session()->has('alert'))
    @php($alertTypes = ['success' => 'alert-success', 'error' => 'alert-error', 'warning' => 'alert-warning', 'info' => 'alert-info'])
    <div class="indicator fixed top-1 left-1 right-1 w-auto">
        <button class="indicator-item indicator-center indicator-bottom badge bg-gray-600" onclick="(e=> e.target.parentNode.remove())(event)">Close</button>
        <div @class(['alert shadow-lg' => true, $alertTypes[session('alert')['type']] => true])>
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none"
                     viewBox="0 0 24 24">
                    @switch(session('alert')['type'])
                        @case('success')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.391-3.016z"/>
                        @break
                        @case('error')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h9.876C19.103 13.59 20 12.31 20 11c0-4.418-3.582-8-8-8a8 8 0 00-8 8c0 1.69.715 3.38 1.946 4.688l-7.908 7.789A3 3 0 003 11c0 4.418 3.582 8 8 8z"/>
                        @break
                        @case('warning')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a6 6 0 00-7.072 0l-4 4a6 6 0 107.072 7.072l1.102-1.101M19.828 5.828l-4-4m4 4l4-4m-12.172-1.172l4 4m-4-4l4-4"/>
                        @break
                        @case('info')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 2h-2v9H6v2c0 .55.45 1 1 1h9c.55 0 1-.45 1-1V6c0-.55-.45-1-1-1zm0 9h-2V6h2v7z"/>
                        @break
                    @endswitch
                </svg>
                <span>{{ session('alert')['message'] }}</span>
            </div>
        </div>
    </div>
@endif

<!-- Scripts -->
<script src="{{ asset('js/vendor.js') }}" defer></script>
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
