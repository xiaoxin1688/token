<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Neural Nexus - 星云算力')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --nn-background: #070c16;
            --nn-surface: #141927;
            --nn-surface-low: #0d1320;
            --nn-surface-high: #1c2332;
            --nn-surface-bright: #253047;
            --nn-outline: rgba(255, 255, 255, 0.08);
            --nn-primary: #a1faff;
            --nn-primary-strong: #00e5ee;
            --nn-secondary: #ac89ff;
            --nn-tertiary: #a0ffc3;
            --nn-text: #e1e5f6;
            --nn-text-muted: #a6abba;
            --nn-text-dim: #6d7383;
        }

        html {
            scroll-behavior: smooth;
        }

        body.frontend-body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--nn-text);
            background:
                radial-gradient(circle at top left, rgba(161, 250, 255, 0.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(172, 137, 255, 0.12), transparent 24%),
                linear-gradient(180deg, #070c16 0%, #090f1c 42%, #070c16 100%);
        }

        .font-headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .font-label {
            font-family: 'Space Grotesk', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .glass-panel {
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .neural-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(161, 250, 255, 0.08) 1px, transparent 0);
            background-size: 36px 36px;
        }

        .frontend-shell {
            position: relative;
            isolation: isolate;
        }

        .frontend-shell::before,
        .frontend-shell::after {
            content: '';
            position: fixed;
            inset: auto;
            z-index: -1;
            border-radius: 9999px;
            filter: blur(120px);
            opacity: 0.22;
            pointer-events: none;
        }

        .frontend-shell::before {
            top: 6rem;
            left: -8rem;
            width: 22rem;
            height: 22rem;
            background: var(--nn-primary);
        }

        .frontend-shell::after {
            right: -10rem;
            bottom: 8rem;
            width: 28rem;
            height: 28rem;
            background: var(--nn-secondary);
        }
    </style>

    @stack('head')
</head>
<body class="frontend-body selection:bg-cyan-300/20 selection:text-cyan-100">
<div class="frontend-shell neural-grid">
    @include('partials.frontend-header')

    <main>
        @yield('content')
    </main>

    @include('partials.frontend-footer')
</div>

@stack('scripts')
</body>
</html>
