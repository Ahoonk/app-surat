<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#020617">

        <title inertia>{{ config('app.name', 'PT ASKARYA') }}</title>

        @vite(['resources/css/app.css', 'resources/js/inertia.js'])
        @inertiaHead
    </head>
    <body class="antialiased bg-slate-950 text-slate-100">
        @inertia
    </body>
</html>
