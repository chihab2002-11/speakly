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
            --lumina-bg-main: #F6FBF5;
            --lumina-bg-section: #F3F8F5;
            --lumina-bg-card: #F0F5EE;
            --lumina-sidebar-bg: #BDD9CE;
            
            /* Text Colors */
            --lumina-text-primary: #181D19;
            --lumina-text-secondary: #3F4941;
            --lumina-text-muted: #64748B;
            --lumina-text-heading: #446651;
            
            /* Accent Colors */
            --lumina-accent-green: #99F6BF;
            --lumina-accent-green-bg: #D1FAE5;
            --lumina-accent-red: #BA1A1A;
            
            /* Border Colors */
            --lumina-border: #E2E8F0;
            --lumina-border-light: rgba(190, 201, 191, 0.15);
        }
    </style>
</head>
<body class="min-h-screen font-inter" style="background-color: var(--lumina-bg-main);">
    <div class="flex min-h-screen">
        {{-- Main Content Area (Full Width - No Sidebar for Admin) --}}
        <div class="flex flex-1 flex-col">
            {{-- Top Navigation Header --}}
            <header 
                class="sticky top-0 z-30 flex items-center justify-between border-b px-4 py-3 md:px-8"
                style="background: rgba(255, 255, 255, 0.8); border-color: rgba(226, 232, 240, 0.5); backdrop-filter: blur(12px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
            >
                {{-- Left Side: Logo/Title --}}
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold" style="color: var(--lumina-primary);">
                        Lumina Academy
                    </h1>
                    <span class="hidden text-sm md:inline" style="color: var(--lumina-text-muted);">Admin Panel</span>
                </div>

                {{-- Right Side: Actions & User Profile --}}
                <div class="flex items-center gap-4 md:gap-6">
                    {{-- Icon Buttons --}}
                    <div class="flex items-center gap-2 md:gap-4">
                        {{-- Messages --}}
                        <a 
                            href="{{ route('role.messages.index', ['role' => 'admin']) }}" 
                            class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </a>

                        {{-- Notifications Button --}}
                        <a href="{{ route('admin.notifications') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php
                                $unreadNotificationsCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
                            @endphp
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>

                        {{-- Dashboard Button --}}
                        <a 
                            href="{{ route('role.dashboard', ['role' => 'admin']) }}" 
                            class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </a>
                    </div>

                    {{-- Vertical Divider --}}
                    <div class="hidden h-8 w-px md:block" style="background-color: var(--lumina-border);"></div>

                    {{-- User Profile --}}
                    <div class="flex items-center gap-3">
                        {{-- User Info (hidden on mobile) --}}
                        <div class="hidden flex-col items-end md:flex">
                            <span class="text-xs font-medium" style="color: #0F172A;">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="text-[10px] font-medium" style="color: var(--lumina-text-muted);">
                                Admin
                            </span>
                        </div>
                        
                        {{-- Avatar --}}
                        <div class="h-10 w-10 overflow-hidden rounded-full border" style="border-color: var(--lumina-border);">
                            @if(auth()->user() && auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm font-semibold" style="background-color: var(--lumina-primary); color: white;">
                                    {{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Vertical Divider --}}
                    <div class="hidden h-8 w-px md:block" style="background-color: var(--lumina-border);"></div>

                    {{-- Logout Button --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button 
                            type="submit" 
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-red-50 cursor-pointer"
                            style="color: var(--lumina-accent-red);"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden md:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Main Scrollable Content --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-8" style="background-color: var(--lumina-bg-section);">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
