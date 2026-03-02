<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sistem Distribusi Dhardhes</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-image: url('{{ asset('logo.svg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>

<body class="min-h-screen flex justify-center">

    <div class="mt-64 bg-black/60 backdrop-blur-sm p-8 rounded-xl text-center text-white max-w-md w-full mx-4">

        <h1 class="text-3xl font-bold mb-4">
            Sistem Distribusi Dhardhes
        </h1>

        <p class="text-sm mb-6">
            Manajemen stok, konsinyasi, dan kontrol sales.
        </p>

        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="block bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg text-sm font-medium transition">
                    Masuk ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="block bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg text-sm font-medium transition">
                    Login Sistem
                </a>
            @endauth
        @endif

    </div>

</body>
</html>