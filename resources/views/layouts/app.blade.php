<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Maintenance Tracker') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Mobile-friendly touch target sizing */
        .btn, .nav-link {
            min-height: 44px;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-gear-wide-connected me-1"></i> MaintenanceApp
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mobileNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 mt-3 mt-lg-0">
                <li class="nav-item mb-2 mb-lg-0">
                        <span class="nav-link text-white-50 px-3">
                            <i class="bi bi-person-badge"></i> {{ Auth::user()->name ?? 'User' }}
                        </span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100 text-start px-3">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

@if (isset($header))
    <header class="bg-white shadow-sm mb-4">
        <div class="container py-3 fw-bold text-secondary">
            {{ $header }}
        </div>
    </header>
@endif

<main class="container py-4 pb-5">
    @if(isset($slot))
        {{ $slot }}
    @else
        @yield('content')
    @endif
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
