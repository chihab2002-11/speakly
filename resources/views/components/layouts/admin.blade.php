<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Dashboard' }} - {{ config('app.name', 'Lumina Academy') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --lumina-primary: #2D8C5E;
            --lumina-primary-dark: #034C3C;
            --lumina-bg-main: #F3F8F5;
            --lumina-sidebar-bg: #BDD9CE;
            --lumina-card-bg: #FFFFFF;
            --lumina-border: #E2E8F0;
            --lumina-text-primary: #181D19;
            --lumina-text-secondary: #3F4941;
            --lumina-text-muted: #64748B;
            --lumina-accent-red: #BA1A1A;
            --lumina-danger: #BA1A1A;
        }

        .btn-press:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body class="min-h-screen font-inter" style="background-color: var(--lumina-bg-main);">
    <div class="flex min-h-screen">
        <x-admin.sidebar :current-route="$currentRoute ?? 'role.dashboard'" />

        <div class="flex min-h-screen flex-1 flex-col lg:ml-64">
            <x-admin.header :user="$user ?? auth()->user()" />

            <main class="flex-1 p-4 md:p-8" style="background-color: var(--lumina-bg-main);">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="admin-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden" onclick="toggleAdminSidebar()"></div>

    <x-live-notifications />

    <script>
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');

            if (!sidebar || !overlay) {
                return;
            }

            if (!sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
