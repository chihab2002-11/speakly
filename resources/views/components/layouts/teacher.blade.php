<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Teacher Dashboard' }} - {{ config('app.name', 'Lumina Academy') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- Google Fonts: Inter & Calibri fallback --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Lumina Academy Teacher Portal Styles --}}
    <style>
        :root {
            /* Primary Colors */
            --lumina-primary: #006A41;
            --lumina-primary-dark: #034C3C;
            --lumina-primary-darker: #1E3A2F;
            --lumina-primary-light: #2D8C5E;
            
            /* Background Colors */
            --lumina-bg-main: #F6FBF4;
            --lumina-bg-section: #F3F8F5;
            --lumina-bg-card: #F0F5EE;
            --lumina-sidebar-bg: #BDD9CE;
            
            /* Text Colors */
            --lumina-text-primary: #181D19;
            --lumina-text-secondary: #3F4941;
            --lumina-text-muted: #64748B;
            --lumina-text-heading: #446651;
            
            /* Accent Colors */
            --lumina-accent-green: #10B981;
            --lumina-accent-green-light: #D1FAE5;
            --lumina-accent-green-dark: #065F46;
            --lumina-accent-emerald: #047857;
            --lumina-accent-red: #BA1A1A;
            --lumina-accent-orange: #F59E0B;
            --lumina-accent-yellow: #FCD34D;
            
            /* Status Colors */
            --lumina-status-present: #10B981;
            --lumina-status-late: #F59E0B;
            --lumina-status-absent: #EF4444;
            --lumina-status-online: #10B981;
            --lumina-status-offline: #CBD5E1;
            --lumina-status-away: #FB923C;
            
            /* Border Colors */
            --lumina-border: #E2E8F0;
            --lumina-border-light: rgba(190, 201, 191, 0.15);
            
            /* Chat Colors */
            --lumina-chat-incoming: #F1F5F9;
            --lumina-chat-outgoing: #047857;
        }

        /* Smooth transitions for interactive elements */
        .transition-smooth {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        /* Button press effect */
        .btn-press:active {
            transform: scale(0.98);
        }

        /* Sidebar link hover */
        .sidebar-link {
            position: relative;
            overflow: hidden;
        }
        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255, 255, 255, 0.3);
            transition: width 0.3s ease;
            border-radius: inherit;
        }
        .sidebar-link:hover::before {
            width: 100%;
        }

        /* Input focus ring */
        .input-focus:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 106, 65, 0.15);
        }

        /* Avatar ring animation */
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .avatar-online::after {
            animation: pulse-ring 2s infinite;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 106, 65, 0.2);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 106, 65, 0.4);
        }

        /* Loading skeleton animation */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>
<body class="min-h-screen font-inter antialiased" style="background-color: var(--lumina-bg-main);">
    <div class="flex min-h-screen">
        {{-- Sidebar (Fixed) --}}
        <x-teacher.sidebar :user="$user ?? auth()->user()" :currentRoute="$currentRoute ?? 'dashboard'" />

        {{-- Main Content Area - offset by sidebar width on desktop --}}
        <div class="flex flex-1 flex-col lg:ml-64">
            {{-- Top Navigation Header --}}
            <x-teacher.header :user="$user ?? auth()->user()" />

            {{-- Main Scrollable Content --}}
            <main class="flex-1 p-4 md:p-8" style="background-color: var(--lumina-bg-section);">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 backdrop-blur-sm lg:hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('teacher-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('teacher-sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (!sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        });
    </script>
</body>
</html>
