{{-- Reusable Navigation Component --}}
{{-- Usage: @include('partials.navigation', ['fixed' => true/false]) --}}

@php
    $isFixed = $fixed ?? true;
@endphp

<nav class="{{ $isFixed ? 'fixed top-0' : '' }} w-full z-50 glass-nav border-b border-primary/5 bg-white/80">
    <div class="flex justify-between items-center px-8 py-4 max-w-7xl mx-auto">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            {{-- Graduation Cap SVG --}}
            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                </svg>
            </div>
            <div class="text-2xl font-young-serif tracking-tight">
                <span class="text-primary">Lumina</span> <span class="text-purple">Academy</span>
            </div>
        </a>
        
        {{-- Nav Links --}}
        <div class="hidden md:flex items-center space-x-10">
            <a class="text-on-surface font-medium hover:text-primary transition-colors nav-home-link" href="{{ url('/') }}">Home</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-medium" href="{{ url('/') }}#programs">Programs</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-medium" href="{{ url('/') }}#features">Features</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-medium" href="{{ url('/') }}#testimonials">Reviews</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-medium" href="{{ url('/') }}#about">About</a>
        </div>
        
        {{-- Auth Buttons --}}
        <div class="flex items-center space-x-4">
            <a href="{{ url('/register-login') }}" class="border border-primary text-primary px-7 py-2.5 rounded-full font-bold hover:bg-primary/5 transition-all hover-lift">Login</a>
            <a href="{{ url('/register-login?tab=register') }}" class="bg-primary text-white px-7 py-2.5 rounded-full font-bold shadow-md hover:shadow-lg hover:opacity-95 transition-all hover-lift">Register</a>
        </div>
    </div>
</nav>
