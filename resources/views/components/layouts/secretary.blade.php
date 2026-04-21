<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Secretary Dashboard' }} - {{ config('app.name', 'Lumina Academy') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --lumina-primary: #006A41;
            --lumina-primary-dark: #034C3C;
            --lumina-primary-darker: #1E3A2F;
            --lumina-primary-light: #2D8C5E;

            --lumina-bg-main: #F6FBF4;
            --lumina-bg-section: #F3F8F5;
            --lumina-bg-card: #F0F5EE;
            --lumina-sidebar-bg: #BDD9CE;

            --lumina-text-primary: #181D19;
            --lumina-text-secondary: #3F4941;
            --lumina-text-muted: #64748B;
            --lumina-text-heading: #446651;

            --lumina-accent-green: #10B981;
            --lumina-accent-green-light: #D1FAE5;
            --lumina-accent-green-dark: #065F46;
            --lumina-accent-red: #BA1A1A;

            --lumina-border: #E2E8F0;
            --lumina-border-light: rgba(190, 201, 191, 0.2);
        }

        .btn-press:active {
            transform: scale(0.98);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 106, 65, 0.2);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 106, 65, 0.4);
        }
    </style>
</head>
<body class="min-h-screen font-inter antialiased" style="background-color: var(--lumina-bg-main);">
    @php
        $activeUser = $user ?? auth()->user();
        $isAdminActingAsSecretary = $activeUser && $activeUser->hasRole('admin');
    @endphp

    <div class="flex min-h-screen">
        @if($isAdminActingAsSecretary)
            <x-admin.sidebar :current-route="$currentRoute ?? 'role.dashboard'" />
        @else
            <x-secretary.sidebar :user="$activeUser" :currentRoute="$currentRoute ?? 'role.dashboard'" />
        @endif

        <div class="flex min-w-0 flex-1 flex-col lg:ml-64">
            @if($isAdminActingAsSecretary)
                <x-admin.header :user="$activeUser" />
            @else
                <x-secretary.header :user="$activeUser" />
            @endif

            <main class="min-w-0 flex-1 overflow-x-hidden p-4 md:p-8" style="background-color: var(--lumina-bg-section);">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="admin-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden" onclick="toggleAdminSidebar()"></div>
    <div id="secretary-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden transition-opacity duration-300" onclick="toggleSecretarySidebar()"></div>

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

        function toggleSecretarySidebar() {
            const sidebar = document.getElementById('secretary-sidebar');
            const overlay = document.getElementById('secretary-sidebar-overlay');

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

            const adminSidebar = document.getElementById('admin-sidebar');
            const adminOverlay = document.getElementById('admin-sidebar-overlay');

            if (adminSidebar && adminOverlay && !adminSidebar.classList.contains('-translate-x-full')) {
                adminSidebar.classList.add('-translate-x-full');
                adminOverlay.classList.add('hidden');
            }

            const secretarySidebar = document.getElementById('secretary-sidebar');
            const secretaryOverlay = document.getElementById('secretary-sidebar-overlay');

            if (secretarySidebar && secretaryOverlay && !secretarySidebar.classList.contains('-translate-x-full')) {
                secretarySidebar.classList.add('-translate-x-full');
                secretaryOverlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
