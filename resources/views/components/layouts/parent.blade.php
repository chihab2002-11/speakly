<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Parent Dashboard' }} - {{ config('app.name', 'Lumina Academy') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- Google Fonts: Inter, Young Serif & Calibri fallback --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Young+Serif&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Lumina Academy Custom Styles --}}
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
            --lumina-accent-green: #6EE7B7;
            --lumina-accent-green-light: #A7F3D0;
            --lumina-accent-green-bg: #D1FAE5;
            --lumina-accent-red: #BA1A1A;
            
            /* Dark Green Card */
            --lumina-dark-green: #064E3B;
            --lumina-dark-green-light: #065F46;
            
            /* Border Colors */
            --lumina-border: #E2E8F0;
            --lumina-border-light: rgba(190, 201, 191, 0.15);
            
            /* Child Avatar Colors */
            --lumina-child-1: #C1E6CC;
            --lumina-child-1-text: #476853;
            --lumina-child-2: #DDE1FF;
            --lumina-child-2-text: #001453;
        }
    </style>
</head>
<body class="min-h-screen font-inter" style="background-color: var(--lumina-bg-main);">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-parent.sidebar 
            :user="$user ?? auth()->user()" 
            :currentRoute="$currentRoute ?? 'dashboard'" 
            :children="$children ?? []"
            :selectedChild="$selectedChild ?? null"
            :hideFinancial="$hideFinancial ?? false"
        />

        {{-- Main Content Area --}}
        <div class="flex flex-1 flex-col lg:ml-64">
            {{-- Top Navigation Header --}}
            <x-parent.header :user="$user ?? auth()->user()" :pageTitle="$pageTitle ?? 'Dashboard'" />

            {{-- Main Scrollable Content --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-8" style="background-color: var(--lumina-bg-section);">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden" onclick="toggleSidebar()"></div>

    <x-live-notifications />

    {{-- Child Selector Dropdown --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('parent-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        function toggleChildSelector() {
            const dropdown = document.getElementById('child-selector-dropdown');
            const icon = document.getElementById('child-selector-icon');

            if (!dropdown || !icon) {
                return;
            }
            
            dropdown.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('child-selector-dropdown');
            const button = document.getElementById('child-selector-button');
            
            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
                document.getElementById('child-selector-icon')?.classList.remove('rotate-180');
            }
        });
    </script>
</body>
</html>
