<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Child Portal' }} - {{ config('app.name', 'Lumina Academy') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Young+Serif&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --lumina-primary: #006A41;
            --lumina-primary-dark: #034C3C;
            --lumina-primary-light: #2D8C5E;
            --lumina-bg-main: #F6FBF5;
            --lumina-bg-section: #F3F8F5;
            --lumina-bg-card: #F0F5EE;
            --lumina-sidebar-bg: #BDD9CE;
            --lumina-text-primary: #181D19;
            --lumina-text-secondary: #3F4941;
            --lumina-text-muted: #64748B;
            --lumina-border: #E2E8F0;
            --lumina-border-light: rgba(190, 201, 191, 0.15);
            --lumina-child-1: #C1E6CC;
            --lumina-child-1-text: #476853;
            --lumina-child-2: #DDE1FF;
            --lumina-child-2-text: #001453;
        }
    </style>
</head>
<body class="min-h-screen font-inter antialiased" style="background-color: var(--lumina-bg-main);">
    <div class="flex min-h-screen">
        <x-parent.child-sidebar
            :parent="$portalParent ?? auth()->user()"
            :child="$portalSelectedChild ?? null"
            :children="$portalChildren ?? []"
            :currentRoute="$currentRoute ?? 'dashboard'"
        />

        <div class="flex flex-1 flex-col lg:ml-64">
            <header class="sticky top-0 z-30 flex items-center justify-between px-8 py-4" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(6px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
                <h1 class="text-xl font-black" style="color: #065F46;">{{ $pageTitle ?? 'Child Portal' }}</h1>
                <div class="flex items-center gap-3">
                    @if(!empty($portalSelectedChild['id']))
                        @php
                            $portalUnreadNotificationsCount = auth()->user()?->unreadNotifications()->count() ?? 0;
                        @endphp
                        <a href="{{ route('parent.child.notifications', ['child' => $portalSelectedChild['id']]) }}" class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100" aria-label="Child notifications" data-live-notification-bell>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span data-live-notification-count class="{{ $portalUnreadNotificationsCount > 0 ? '' : 'hidden' }} absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ $portalUnreadNotificationsCount > 9 ? '9+' : $portalUnreadNotificationsCount }}
                            </span>
                        </a>
                        <a href="{{ route('parent.child.settings', ['child' => $portalSelectedChild['id']]) }}" class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100" aria-label="Child settings">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </a>
                    @endif
                    <div class="rounded-full px-3 py-1 text-xs font-bold" style="background: #e8f5ee; color: #0e7a4e;">
                        Parent View
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8" style="background-color: var(--lumina-bg-section);">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="child-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden" onclick="toggleChildPortalSidebar()"></div>

    <x-live-notifications />

    <script>
        function toggleChildPortalSidebar() {
            const sidebar = document.getElementById('parent-child-sidebar');
            const overlay = document.getElementById('child-sidebar-overlay');

            sidebar?.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        }

        function toggleChildPortalSelector() {
            const dropdown = document.getElementById('child-portal-selector-dropdown');
            const icon = document.getElementById('child-portal-selector-icon');

            if (!dropdown || !icon) {
                return;
            }

            dropdown.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('child-portal-selector-dropdown');
            const button = document.getElementById('child-portal-selector-button');

            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
                document.getElementById('child-portal-selector-icon')?.classList.remove('rotate-180');
            }
        });
    </script>
</body>
</html>
