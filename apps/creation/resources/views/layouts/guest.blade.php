<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Xquisite Creations - Business Technology & SaaS Platforms">
<meta name="author" content="Xquisite Creations">
<script src="https://cdn.tailwindcss.com"></script>
<title>
{{ config('app.name', 'Laravel') }}
@isset($currentCompany)
 - {{ $currentCompany->name }}
@endisset
</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Custom Styles -->
<link rel="stylesheet" href="{{ asset('css/style.css') }}">

@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
body {
    font-family: 'Times New Roman', Times, serif;
}

:root {
    --gold: #daa509;
}

.text-gold {
    color: var(--gold);
}

.glass-card {
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(234, 179, 8, 0.3);
}

.nav-link {
    color: #cbd5f5;
    transition: color .2s;
}

.nav-link:hover {
    color: var(--gold);
}
</style>

</head>


<body class="bg-slate-950  antialiased">


<!-- HEADER -->
<header class="border-b border-white/10 backdrop-blur sticky top-0 z-50">

<div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

<div class="font-bold text-lg text-white tracking-wide">
Xquisite Creations
</div>

<nav class="flex items-center space-x-6 text-sm">

@auth

<a class="nav-link" href="{{ route('dashboard') }}">
Dashboard
</a>

<a class="nav-link" href="{{ route('profile.show') }}">
Profile
</a>

<form method="POST" action="{{ route('logout') }}">
@csrf
<button class="nav-link">Logout</button>
</form>

@else

<a class="nav-link" href="{{ route('login') }}">
Login
</a>

<a class="nav-link" href="{{ route('register') }}">
Register
</a>

@endauth

</nav>

</div>

</header>



<!-- PAGE CONTENT -->
<main class="min-h-screen">

<div class="max-w-7xl mx-auto px-6 py-10">

{{ $slot }}

</div>

</main>



<!-- FOOTER -->
<footer class="bg-white text-black border-t mt-20">

<div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">

<div>

<h3 class="font-bold text-lg">
Xquisite Creations
</h3>

<p class="text-gray-600 text-sm mt-2">
Business Technology & SaaS Platforms
</p>

</div>


<div>

<h4 class="font-semibold mb-2">
Products
</h4>

<ul class="text-sm text-gray-600 space-y-1">
<li>Loan Management</li>
<li>ERP Systems</li>
<li>Property Management</li>
</ul>

</div>


<div>

<h4 class="font-semibold mb-2">
Company
</h4>

<ul class="text-sm text-gray-600 space-y-1">
<li>About</li>
<li>Contact</li>
<li>Privacy Policy</li>
</ul>

</div>

</div>


<div class="text-center text-gray-500 text-sm pb-6">
© {{ date('Y') }} Xquisite Creations. All rights reserved.
</div>

</footer>


@livewireScripts

</body>

</html>