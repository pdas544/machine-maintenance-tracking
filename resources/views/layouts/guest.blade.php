<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Machine Maintenance App') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .icon-xs{
                width: 4rem;
                height: 4rem;
            }
        </style>
    </head>
    <body class="">
        <div class="container col-md-4 col-sm-6 col-12 d-flex flex-column justify-content-center">
            <div class="mx-auto">
                <a href="/">
                    <x-application-logo class="icon-xs"/>
                </a>
            </div>

            <div class="border border-1 w-100 p-4 m-2">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
