<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IDReno</title>

    <!-- CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen flex flex-col bg-[#F3F3F3]">

{{-- Si l’utilisateur est connecté, on affiche le header --}}
@auth
    <x-header class="w-full" />
@endauth

<div class="flex flex-1 min-h-0 pt-20">

    {{-- Si l’utilisateur est connecté, on affiche la sidebar --}}
    @auth
        <x-sidebar class="w-64 bg-dark text-white h-full" />
    @endauth

    {{-- Le contenu principal, toujours affiché --}}
    <main class="flex-1 p-0 lg:p-10 overflow-y-auto md:mt-10 lg:mt-0">
        @yield('content')
    </main>

</div>

<x-mobile-menu />

@if(session('success'))
    <script>
        toastr.success("{{ session('success') }}");
    </script>
@endif
</body>
</html>
