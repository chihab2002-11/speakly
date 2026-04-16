<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Lumina Academy | The Cognitive Gallery of Language</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Young+Serif&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2D8C5E",
                        "purple": "#5B4ED4",
                        "purple-light": "#E8E5FF",
                        "on-primary": "#ffffff",
                        "surface": "#ffffff",
                        "on-surface": "#1a1b22",
                        "on-surface-variant": "#444653",
                        "surface-container-low": "#E8F9F1",
                        "surface-container-high": "#A8E6C5",
                        "en-red": "#CA1D1D",
                        "es-orange": "#FF9A6E",
                        "fr-blue": "#0089E3"
                    },
                    fontFamily: {
                        "inter": ["Inter", "sans-serif"],
                        "serif": ["Young Serif", "Georgia", "Cambria", "Times New Roman", "serif"],
                        "young-serif": ["Young Serif", "serif"]
                    },
                    letterSpacing: {
                        "tightest": "-0.9px",
                        "header": "-2.4px"
                    }
                },
            },
        }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; }
        .font-black-900 { font-weight: 900; }
        .bg-grid { background-image: radial-gradient(#000 1px, transparent 0); background-size: 40px 40px; }
        .hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .hover-lift:hover { transform: translateY(-4px); }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-white text-on-surface overflow-x-hidden relative">

@php
    $programs = collect($languagePrograms ?? [])
        ->filter(fn ($program) => $program['is_active'] ?? true)
        ->sortBy('sort_order')
        ->values();

  $visitorReviews = collect($reviews ?? []);
  $votedReviewIds = collect($votedReviewIds ?? [])->map(fn ($id) => (int) $id)->all();

    $languageModalData = $programs
        ->mapWithKeys(function ($program) {
            return [
                $program['code'] => [
                    'name' => $program['name'],
                    'title' => $program['title'],
                    'description' => $program['description'],
                    'fullDescription' => $program['full_description'],
                    'certifications' => $program['certifications'] ?? [],
                ],
            ];
        })
        ->all();
@endphp

<!-- Academic/Language Background Patterns -->
<div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden w-full h-full select-none">
    
    <!-- Floating Language Characters - Multilingual Alphabet -->
    <span class="absolute top-[5%] left-[3%] text-[180px] font-serif font-black text-primary/10 rotate-12">A</span>
    <span class="absolute top-[8%] right-[8%] text-[140px] font-serif font-bold text-primary/[0.08] -rotate-12">あ</span>
    <span class="absolute top-[35%] left-[2%] text-[120px] font-serif font-bold text-primary/[0.09] -rotate-6">Ñ</span>
    <span class="absolute bottom-[15%] right-[5%] text-[200px] font-serif font-black text-primary/10 rotate-[15deg]">¿</span>
    <span class="absolute top-[55%] left-[5%] text-[130px] font-serif font-bold text-primary/[0.08] -rotate-[15deg]">文</span>
    <span class="absolute bottom-[35%] left-[8%] text-[110px] font-serif font-bold text-primary/[0.09] rotate-[8deg]">ß</span>
    <span class="absolute top-[75%] right-[12%] text-[150px] font-serif font-black text-primary/10 -rotate-[10deg]">Б</span>
    <span class="absolute top-[18%] left-[25%] text-[100px] font-serif font-bold text-primary/[0.07] rotate-[20deg]">α</span>
    <span class="absolute bottom-[8%] left-[35%] text-[160px] font-serif font-black text-primary/[0.09] rotate-6">Ç</span>
    <span class="absolute top-[45%] right-[3%] text-[90px] font-serif font-bold text-primary/[0.08] -rotate-[25deg]">한</span>
    <span class="absolute bottom-[55%] right-[20%] text-[85px] font-serif font-bold text-primary/[0.07] rotate-[12deg]">ع</span>
    <span class="absolute top-[12%] left-[45%] text-[75px] font-serif font-bold text-primary/[0.06] -rotate-[8deg]">É</span>
    
    <!-- Academic Icons (Using Material Symbols) -->
    <span class="material-symbols-outlined absolute top-[22%] left-[12%] text-[140px] text-primary/10 rotate-6">forum</span>
    <span class="material-symbols-outlined absolute top-[10%] right-[20%] text-[180px] text-primary/[0.09] -rotate-12">menu_book</span>
    <span class="material-symbols-outlined absolute bottom-[18%] right-[30%] text-[200px] text-primary/10 rotate-12">translate</span>
    <span class="material-symbols-outlined absolute top-[62%] right-[6%] text-[160px] text-primary/[0.09] rotate-[15deg]">school</span>
    <span class="material-symbols-outlined absolute bottom-[3%] left-[18%] text-[180px] text-primary/10 -rotate-12">public</span>
    <span class="material-symbols-outlined absolute top-[38%] right-[35%] text-[120px] text-primary/[0.08] -rotate-6">record_voice_over</span>
    <span class="material-symbols-outlined absolute top-[50%] left-[30%] text-[100px] text-primary/[0.07] rotate-[18deg]">headphones</span>
    <span class="material-symbols-outlined absolute bottom-[40%] left-[45%] text-[130px] text-primary/[0.08] -rotate-[15deg]">edit_note</span>
    <span class="material-symbols-outlined absolute top-[80%] left-[55%] text-[110px] text-primary/[0.07] rotate-[22deg]">psychology</span>
    <span class="material-symbols-outlined absolute top-[5%] left-[60%] text-[90px] text-primary/[0.06] -rotate-[20deg]">lightbulb</span>
    <span class="material-symbols-outlined absolute bottom-[25%] right-[8%] text-[140px] text-primary/[0.09] rotate-8">chat</span>
    <span class="material-symbols-outlined absolute top-[28%] right-[45%] text-[80px] text-primary/[0.06] -rotate-[12deg]">library_books</span>
    <span class="material-symbols-outlined absolute bottom-[60%] left-[60%] text-[95px] text-primary/[0.07] rotate-[25deg]">grading</span>
    <span class="material-symbols-outlined absolute top-[68%] left-[10%] text-[120px] text-primary/[0.08] -rotate-[8deg]">military_tech</span>
    
    <!-- Geometric Shapes - Circles -->
    <div class="absolute top-[15%] left-[8%] w-40 h-40 border-[3px] border-primary/10 rounded-full"></div>
    <div class="absolute top-[30%] right-[10%] w-56 h-56 border-[4px] border-primary/[0.08] rounded-full"></div>
    <div class="absolute bottom-[20%] left-[25%] w-32 h-32 border-[3px] border-primary/[0.09] rounded-full"></div>
    <div class="absolute top-[60%] right-[25%] w-24 h-24 border-[2px] border-primary/[0.07] rounded-full"></div>
    <div class="absolute bottom-[45%] right-[40%] w-20 h-20 border-[2px] border-primary/[0.06] rounded-full"></div>
    
    <!-- Geometric Shapes - Squares and Rectangles -->
    <div class="absolute top-[40%] left-[15%] w-28 h-28 border-[3px] border-primary/[0.09] rotate-45"></div>
    <div class="absolute bottom-[30%] right-[15%] w-44 h-44 border-[4px] border-primary/10 rotate-12"></div>
    <div class="absolute top-[72%] left-[40%] w-20 h-20 border-[2px] border-primary/[0.07] rotate-[30deg]"></div>
    <div class="absolute top-[8%] right-[35%] w-36 h-36 border-[3px] border-primary/[0.08] -rotate-[15deg]"></div>
    
    <!-- Geometric Shapes - Rounded Rectangles -->
    <div class="absolute top-[25%] left-[50%] w-32 h-16 border-[3px] border-primary/[0.08] rounded-xl rotate-[10deg]"></div>
    <div class="absolute bottom-[12%] right-[45%] w-40 h-20 border-[3px] border-primary/[0.09] rounded-2xl -rotate-[8deg]"></div>
    <div class="absolute top-[85%] left-[15%] w-28 h-14 border-[2px] border-primary/[0.07] rounded-lg rotate-[15deg]"></div>
    
    <!-- Decorative Lines and Paths -->
    <svg class="absolute top-[20%] left-[35%] w-64 h-64 text-primary/[0.07]" viewBox="0 0 200 200" fill="none">
        <path d="M20,100 Q60,20 100,100 T180,100" stroke="currentColor" stroke-width="3" fill="none"/>
    </svg>
    <svg class="absolute bottom-[35%] right-[5%] w-48 h-48 text-primary/[0.08]" viewBox="0 0 150 150" fill="none">
        <circle cx="75" cy="75" r="60" stroke="currentColor" stroke-width="2" stroke-dasharray="10 5" fill="none"/>
    </svg>
    <svg class="absolute top-[55%] left-[55%] w-40 h-40 text-primary/[0.07]" viewBox="0 0 120 120" fill="none">
        <polygon points="60,10 110,90 10,90" stroke="currentColor" stroke-width="2" fill="none"/>
    </svg>
    
    <!-- Speech Bubbles -->
    <svg class="absolute top-[15%] left-[65%] w-24 h-24 text-primary/[0.09]" viewBox="0 0 100 100" fill="none">
        <path d="M10,10 h60 a10,10 0 0,1 10,10 v30 a10,10 0 0,1 -10,10 h-40 l-15,15 v-15 h-5 a10,10 0 0,1 -10,-10 v-30 a10,10 0 0,1 10,-10" stroke="currentColor" stroke-width="3" fill="none"/>
    </svg>
    <svg class="absolute bottom-[50%] left-[3%] w-20 h-20 text-primary/[0.08] rotate-12" viewBox="0 0 100 100" fill="none">
        <path d="M90,10 h-60 a10,10 0 0,0 -10,10 v30 a10,10 0 0,0 10,10 h40 l15,15 v-15 h5 a10,10 0 0,0 10,-10 v-30 a10,10 0 0,0 -10,-10" stroke="currentColor" stroke-width="3" fill="none"/>
    </svg>
    
    <!-- Book Icons -->
    <svg class="absolute top-[42%] left-[70%] w-28 h-28 text-primary/[0.08] -rotate-6" viewBox="0 0 100 100" fill="none">
        <rect x="15" y="10" width="70" height="80" rx="3" stroke="currentColor" stroke-width="3" fill="none"/>
        <line x1="50" y1="10" x2="50" y2="90" stroke="currentColor" stroke-width="2"/>
        <path d="M15,20 Q32,25 50,20" stroke="currentColor" stroke-width="1.5" fill="none"/>
        <path d="M50,20 Q68,25 85,20" stroke="currentColor" stroke-width="1.5" fill="none"/>
    </svg>
    
    <!-- Pencil Icon -->
    <svg class="absolute bottom-[8%] right-[25%] w-24 h-24 text-primary/[0.09] rotate-[35deg]" viewBox="0 0 100 100" fill="none">
        <rect x="25" y="10" width="20" height="70" rx="2" stroke="currentColor" stroke-width="3" fill="none"/>
        <polygon points="35,80 25,95 45,95" stroke="currentColor" stroke-width="2" fill="none"/>
        <line x1="25" y1="20" x2="45" y2="20" stroke="currentColor" stroke-width="2"/>
    </svg>
    
    <!-- Globe with meridians -->
    <svg class="absolute top-[78%] right-[38%] w-32 h-32 text-primary/[0.08]" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="2" fill="none"/>
        <ellipse cx="50" cy="50" rx="20" ry="40" stroke="currentColor" stroke-width="1.5" fill="none"/>
        <line x1="10" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="1.5"/>
        <path d="M15,30 Q50,35 85,30" stroke="currentColor" stroke-width="1" fill="none"/>
        <path d="M15,70 Q50,65 85,70" stroke="currentColor" stroke-width="1" fill="none"/>
    </svg>
    
    <!-- Graduation Cap -->
    <svg class="absolute top-[3%] left-[80%] w-28 h-28 text-primary/[0.09] rotate-[-10deg]" viewBox="0 0 100 100" fill="none">
        <polygon points="50,15 10,35 50,55 90,35" stroke="currentColor" stroke-width="2.5" fill="none"/>
        <path d="M25,40 v25 Q50,80 75,65 v-25" stroke="currentColor" stroke-width="2" fill="none"/>
        <line x1="85" y1="35" x2="85" y2="65" stroke="currentColor" stroke-width="2"/>
        <circle cx="85" cy="68" r="4" stroke="currentColor" stroke-width="1.5" fill="none"/>
    </svg>
    
    <!-- Certificate/Diploma -->
    <svg class="absolute bottom-[65%] left-[80%] w-24 h-24 text-primary/[0.07] rotate-[8deg]" viewBox="0 0 100 100" fill="none">
        <rect x="10" y="20" width="80" height="55" rx="3" stroke="currentColor" stroke-width="2.5" fill="none"/>
        <line x1="25" y1="35" x2="75" y2="35" stroke="currentColor" stroke-width="1.5"/>
        <line x1="25" y1="45" x2="75" y2="45" stroke="currentColor" stroke-width="1.5"/>
        <line x1="30" y1="55" x2="70" y2="55" stroke="currentColor" stroke-width="1.5"/>
        <circle cx="50" cy="85" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
    </svg>
    
    <!-- Audio Wave -->
    <svg class="absolute top-[88%] left-[70%] w-36 h-16 text-primary/[0.08]" viewBox="0 0 150 50" fill="none">
        <path d="M10,25 Q20,5 30,25 T50,25 T70,25 T90,25 T110,25 T130,25 T150,25" stroke="currentColor" stroke-width="2.5" fill="none"/>
    </svg>
    
    <!-- Dots Pattern -->
    <div class="absolute top-[32%] left-[88%] grid grid-cols-4 gap-3 text-primary/10">
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
        <div class="w-2 h-2 rounded-full bg-current"></div>
    </div>
    
    <!-- ABC Letters decorative -->
    <div class="absolute bottom-[75%] left-[92%] flex flex-col gap-1 text-primary/[0.08] font-bold text-2xl -rotate-[10deg]">
        <span>A</span>
        <span>B</span>
        <span>C</span>
    </div>
    
    <!-- Language Quotation Marks -->
    <span class="absolute top-[48%] left-[92%] text-[80px] font-serif text-primary/[0.09] rotate-[15deg]">"</span>
    <span class="absolute bottom-[22%] left-[48%] text-[60px] font-serif text-primary/[0.07] -rotate-[10deg]">„</span>
    
    <!-- Plus signs decorative -->
    <span class="absolute top-[65%] left-[78%] text-[50px] font-light text-primary/[0.07]">+</span>
    <span class="absolute bottom-[48%] left-[22%] text-[40px] font-light text-primary/[0.06]">+</span>
    <span class="absolute top-[90%] right-[55%] text-[35px] font-light text-primary/[0.06]">+</span>
    
    <!-- Star shapes -->
    <svg class="absolute top-[58%] left-[85%] w-12 h-12 text-primary/[0.08]" viewBox="0 0 50 50" fill="none">
        <polygon points="25,5 30,20 45,20 33,30 38,45 25,35 12,45 17,30 5,20 20,20" stroke="currentColor" stroke-width="2" fill="none"/>
    </svg>
    <svg class="absolute bottom-[85%] left-[38%] w-10 h-10 text-primary/[0.07]" viewBox="0 0 50 50" fill="none">
        <polygon points="25,5 30,20 45,20 33,30 38,45 25,35 12,45 17,30 5,20 20,20" stroke="currentColor" stroke-width="1.5" fill="none"/>
    </svg>
</div>

@include('partials.navigation', ['fixed' => true])
<main class="pt-20">
<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center px-8 overflow-hidden">
<!-- Background Motifs -->
<div class="absolute inset-0 bg-grid opacity-[0.03] pointer-events-none"></div>
<div class="absolute right-[10%] top-1/4 opacity-[0.05] pointer-events-none">
<span class="material-symbols-outlined text-[400px]">explore</span>
</div>
<div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-16 items-center relative z-10">
<div>
<div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-surface-container-low text-primary font-bold text-sm mb-8 border border-primary/10">
<span class="material-symbols-outlined text-[18px]">verified</span>
                    Award-winning Language Curriculum
                </div>
<h1 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] mb-10">
                    Your next<br/><span class="text-purple italic">language</span><br/>starts today.
                </h1>
<p class="text-xl md:text-2xl text-on-surface-variant max-w-xl mb-12 leading-relaxed font-light">
                    Step beyond standard learning. Experience an editorial approach to language mastery where every lesson is a curated exhibit of culture and fluency.
                </p>
<div class="flex flex-wrap justify-center lg:justify-start items-center gap-6">
<a href="/register-login?tab=register" class="bg-primary text-white px-14 py-6 rounded-full font-black text-xl shadow-xl shadow-primary/20 hover-lift active:scale-95 transition-all text-center">Join Our School</a>
<a class="flex items-center justify-center gap-3 font-bold text-on-surface hover:text-primary transition-colors group text-lg" href="#pricing">
                        Contact Us 
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
</div>
</div>
<div class="relative">
<div class="relative z-10 aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl transform lg:rotate-3 hover:rotate-0 transition-transform duration-700">
<img alt="Collaborative learning in a modern environment" 
     class="w-full h-full object-cover" 
     src="{{ asset('images/learners.jpg') }}"/></div>
<div class="absolute -bottom-10 -left-10 z-20 bg-white p-6 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] max-w-[280px] hover-lift">
<div class="flex items-center gap-4 mb-3">
<div class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-primary">
<span class="material-symbols-outlined">menu_book</span>
</div>
<div class="font-bold leading-tight">Authentic Artifacts</div>
</div>
<p class="text-sm text-on-surface-variant">We study using original manuscripts and modern digital archives from the world's great libraries.</p>
</div>
<!-- Geometric Overlay Motif -->
<div class="absolute -top-12 -right-12 w-48 h-48 border-[1px] border-primary/10 rounded-full pointer-events-none"></div>
<div class="absolute -top-6 -right-6 w-48 h-48 border-[1px] border-primary/5 rounded-full pointer-events-none"></div>
</div>
</div>
</section>
<!-- Programs Section -->
<section class="py-32 px-8 relative overflow-hidden" id="programs">
<div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary/40 to-transparent shadow-md shadow-primary/30"></div>
<div class="absolute inset-0 bg-grid opacity-[0.02] pointer-events-none"></div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="mb-24 relative flex items-end justify-center">
<div class="flex flex-col items-center text-center space-y-6 w-full mx-auto">
<div class="flex items-center justify-center gap-8 w-full">
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
<h2 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] shrink-0">World-class <br/><span class="text-purple italic">programs</span>.</h2>
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
</div>
<p class="text-on-surface-variant text-xl leading-relaxed font-light max-w-2xl">Curated pathways for every ambition, from conversational ease to professional certification, delivered by industry experts.</p>
</div>
<div class="hidden md:flex gap-4 absolute right-0 bottom-2">
<button class="slider-arrow-left w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center cursor-pointer hover:bg-primary hover:text-white transition-all" data-direction="left">
<span class="material-symbols-outlined">west</span>
</button>
<button class="slider-arrow-right w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center cursor-pointer hover:bg-primary hover:text-white transition-all" data-direction="right">
<span class="material-symbols-outlined">east</span>
</button>
</div>
</div>
<div class="relative overflow-hidden">
<div class="programs-slider flex gap-6 transition-transform duration-300 ease-out select-none" id="programsSlider" style="width: fit-content;">
@foreach ($programs as $program)
<div class="slider-card bg-slate-50 p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer"
     data-lang="{{ $program['code'] }}">
<div class="mb-10 relative">
<img alt="{{ $program['name'] }} Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="{{ $program['flag_url'] }}"/>
<div class="absolute -top-2 -right-2 text-primary font-black-900 text-2xl">{{ strtoupper($program['code']) }}</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">{{ $program['title'] }}</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">{{ $program['description'] }}</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>
@endforeach
</div>
</div>
</div>
</section>
<!-- Features/Portal Section -->
<section class="py-32 px-8 relative overflow-hidden" id="features">
<div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary/40 to-transparent shadow-md shadow-primary/30"></div>
<div class="absolute inset-0 bg-grid opacity-[0.02] pointer-events-none"></div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="text-center mb-24 space-y-6 w-full mx-auto flex flex-col items-center">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Portal Benefits</span>
<div class="flex items-center justify-center gap-8 w-full">
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
<h2 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] shrink-0 mb-4">Tools for the <br/><span class="text-purple italic">modern scholar</span>.</h2>
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-12 gap-8 h-auto md:h-[650px]">
<div class="md:col-span-8 bg-surface-container-low p-12 rounded-[40px] flex flex-col justify-between overflow-hidden shadow-sm border border-black/5 hover-lift transition-all duration-500">
<div class="max-w-md">
<h3 class="text-3xl font-black-900 mb-6 text-on-surface">Personalized Student <br/>Gallery</h3>
<p class="text-on-surface-variant text-lg leading-relaxed font-light">Your academic journey, beautifully curated. Track your growth through an editorial lens with integrated resource mapping.</p>
</div>
<div class="mt-12 -mb-16 -mr-16 rounded-tl-[40px] overflow-hidden shadow-2xl border-l-4 border-t-4 border-white transform translate-x-4 translate-y-4 hover:translate-x-0 hover:translate-y-0 transition-transform duration-700">
<img alt="Dashboard showing student progress and library access" class="w-full" src="{{ asset('images/student_progress.png')}}"/>
</div>
</div>
<div class="md:col-span-4 bg-primary text-white p-10 rounded-[40px] flex flex-col items-center justify-center text-center shadow-xl shadow-primary/20 hover-lift group">
<div class="mb-8 w-24 h-24 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-md border border-white/20 group-hover:scale-110 transition-transform duration-500">
<span class="material-symbols-outlined text-[48px]">auto_graph</span>
</div>
<h3 class="text-2xl font-bold mb-6">Cognitive Progress</h3>
<p class="opacity-80 text-base leading-relaxed font-light">Advanced skill-gap analysis and visual attendance heatmaps updated instantly after every curated session.</p>
</div>
<div class="md:col-span-5 bg-surface-container-high p-10 rounded-[40px] flex items-center gap-8 border border-white/40 shadow-sm hover-lift">
<div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center shrink-0 shadow-lg rotate-3 group-hover:rotate-0 transition-all">
<span class="material-symbols-outlined text-primary text-[32px]">voice_chat</span>
</div>
<div>
<h3 class="text-xl font-black-900 text-on-surface mb-2">Concierge Access</h3>
<p class="text-on-surface-variant text-sm leading-relaxed font-medium">Direct 24/7 line to your master instructors for scholarly clarification.</p>
</div>
</div>
<div class="md:col-span-7 bg-surface-container-high p-10 rounded-[40px] border border-white/40 flex flex-col justify-center shadow-sm hover-lift relative overflow-hidden group">
<div class="absolute -right-10 -top-10 opacity-[0.05] group-hover:scale-110 transition-transform duration-700">
<span class="material-symbols-outlined text-[200px]">history_edu</span>
</div>
<div class="flex justify-between items-center relative z-10">
<div class="space-y-3">
<h3 class="text-2xl font-black-900 text-on-surface">Digital Archive</h3>
<p class="text-on-surface-variant text-base font-light">Over 5,000+ digital assets, interactive manuscripts, and historical quizzes.</p>
</div>
<div class="bg-white/50 p-4 rounded-2xl backdrop-blur-sm">
<span class="material-symbols-outlined text-[40px] text-primary">collections_bookmark</span>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Testimonials Section -->
<section class="py-32 px-8 relative overflow-hidden" id="testimonials">
<div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary/40 to-transparent shadow-md shadow-primary/30"></div>
<div class="absolute inset-0 bg-grid opacity-[0.02] pointer-events-none"></div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="mb-24 relative flex items-end justify-center">
<div class="flex flex-col items-center text-center space-y-6 w-full mx-auto">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Reviews</span>
<div class="flex items-center justify-center gap-8 w-full">
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
<h2 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] mb-6 shrink-0">Voices of <br/><span class="text-purple italic">Lumina</span>.</h2>
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
</div>
<p class="text-on-surface-variant text-xl leading-relaxed font-light max-w-2xl">Hear from our scholars who've transformed their linguistic journeys at Lumina Academy.</p>
</div>
<div class="hidden md:flex gap-4 absolute right-0 bottom-2">
<button class="reviews-arrow-left w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center cursor-pointer hover:bg-primary hover:text-white transition-all" data-direction="left">
<span class="material-symbols-outlined">west</span>
</button>
<button class="reviews-arrow-right w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center cursor-pointer hover:bg-primary hover:text-white transition-all" data-direction="right">
<span class="material-symbols-outlined">east</span>
</button>
</div>
</div>
<div class="relative overflow-hidden">
<div class="reviews-slider flex gap-6 transition-transform duration-300 ease-out select-none pb-4" id="reviewsSlider" style="width: fit-content;">
@forelse($visitorReviews as $review)
@php
  $reviewId = (int) $review->id;
  $isVoted = in_array($reviewId, $votedReviewIds, true);
  $studentName = (string) ($review->student_name ?: 'Lumina Student');
  $studentInitials = \Illuminate\Support\Str::of($studentName)
    ->explode(' ')
    ->take(2)
    ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
    ->implode('');
  $studentGroup = (string) ($review->student_group ?: 'Group not set');
  $uploadDate = $review->uploaded_at ?? $review->created_at;
  $studentAvatar = (string) ($review->profile_picture_url ?? '');
@endphp
<article class="review-card relative bg-slate-50/95 backdrop-blur-sm rounded-[32px] p-8 border border-primary/10 shadow-[0_25px_45px_-35px_rgba(45,140,94,0.6)] hover-lift flex flex-col overflow-hidden flex-shrink-0 w-96" data-review-id="{{ $reviewId }}">
<div class="relative z-10 mb-6">
<div class="mb-4 flex items-center justify-between gap-3">
<span class="text-xs font-bold text-primary bg-primary/10 px-3 py-1 rounded-full review-rating-value">{{ number_format((float) $review->rating_score, 1) }}/5</span>
<div class="flex items-center gap-2">
<button class="review-vote-btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-primary/20 text-primary transition-all hover:bg-primary hover:text-white disabled:cursor-not-allowed disabled:opacity-50" data-vote="like" {{ $isVoted ? 'disabled' : '' }}>
<span class="material-symbols-outlined text-[16px]">thumb_up</span>
</button>
<button class="review-vote-btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-primary/20 text-primary transition-all hover:bg-primary hover:text-white disabled:cursor-not-allowed disabled:opacity-50" data-vote="dislike" {{ $isVoted ? 'disabled' : '' }}>
<span class="material-symbols-outlined text-[16px]">thumb_down</span>
</button>
</div>
</div>
<p class="text-on-surface-variant text-base leading-relaxed font-medium">"{{ $review->review_text }}"</p>
</div>
<div class="relative z-10 flex items-center gap-4 mt-auto pt-6 border-t border-primary/10">
@if($studentAvatar !== '')
<img src="{{ $studentAvatar }}" alt="{{ $studentName }}" class="w-14 h-14 rounded-2xl object-cover">
@else
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-purple flex items-center justify-center text-white font-black">{{ $studentInitials !== '' ? $studentInitials : 'ST' }}</div>
@endif
<div>
<p class="font-bold text-on-surface">{{ $studentName }}</p>
<p class="text-sm text-on-surface-variant">{{ $studentGroup }}</p>
<p class="text-xs text-on-surface-variant/80">{{ $uploadDate ? $uploadDate->format('M d, Y') : '' }}</p>
</div>
</div>
</article>
@empty
<article class="review-card relative bg-slate-50/95 backdrop-blur-sm rounded-[32px] p-8 border border-primary/10 shadow-[0_25px_45px_-35px_rgba(45,140,94,0.6)] hover-lift flex flex-col overflow-hidden flex-shrink-0 w-96">
<div class="relative z-10 mb-6">
<div class="mb-4">
<span class="text-xs font-bold text-primary bg-primary/10 px-3 py-1 rounded-full">0.0/5</span>
</div>
<p class="text-on-surface-variant text-base leading-relaxed font-medium">"No reviews yet. Be the first student to share an experience."</p>
</div>
<div class="relative z-10 flex items-center gap-4 mt-auto pt-6 border-t border-primary/10">
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-purple flex items-center justify-center text-white font-black">NA</div>
<div>
<p class="font-bold text-on-surface">Lumina Student</p>
<p class="text-sm text-on-surface-variant">Group not set</p>
<p class="text-xs text-on-surface-variant/80">{{ now()->format('M d, Y') }}</p>
</div>
</div>
</article>
@endforelse
</div>
</div>
</section>
<!-- Pricing Section -->
<section class="py-32 px-8 relative overflow-hidden" id="pricing">
<div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary/40 to-transparent shadow-md shadow-primary/30"></div>
<div class="absolute inset-0 bg-grid opacity-[0.02] pointer-events-none"></div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="text-center mb-24 space-y-6 w-full mx-auto flex flex-col items-center">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Pricing Plans</span>
<div class="flex items-center justify-center gap-8 w-full">
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
<h2 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] mb-6 shrink-0">Investment in <br/><span class="text-purple italic">your future</span>.</h2>
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
</div>
<p class="text-on-surface-variant text-xl leading-relaxed max-w-2xl mx-auto font-light">Choose the language course that best fits your learning goals and linguistic ambitions.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
<!-- IELTS Course -->
<div class="bg-slate-50 rounded-3xl p-8 border border-black/5 hover-lift flex flex-col">
<div class="mb-8">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-sm mb-4">
<span class="material-symbols-outlined text-[16px]">trending_up</span>
IELTS Preparation
</div>
<h3 class="text-2xl font-bold text-on-surface mb-2">IELTS Mastery</h3>
<p class="text-on-surface-variant text-sm font-light">Comprehensive preparation for International English Language Testing System</p>
</div>
<div class="mb-8">
<div class="text-3xl font-bold text-on-surface">7,000 <span class="text-lg text-on-surface-variant">DA/course</span></div>
<p class="text-sm text-on-surface-variant mt-2">Available for all proficiency levels</p>
</div>
<div class="space-y-2 mb-8 flex-grow">
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">A1 - A2:</span>
<span class="text-on-surface-variant text-sm">Foundation & Elementary</span>
</div>
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">B1 - B2:</span>
<span class="text-on-surface-variant text-sm">Intermediate & Upper Intermediate</span>
</div>
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">C1 - C2:</span>
<span class="text-on-surface-variant text-sm">Advanced & Mastery</span>
</div>
</div>
<ul class="space-y-4 mb-8 flex-grow">
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">12-week intensive program</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Mock exam practice tests</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Expert listening & speaking training</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Writing feedback & correction</span>
</li>
</ul>
</div>

<!-- Contact Card (Middle) -->
<div class="bg-gradient-to-br from-primary to-primary/80 rounded-3xl p-8 flex flex-col justify-between items-center text-center text-white hover-lift overflow-hidden">
<div class="w-full mb-6">
<div class="w-full h-48 rounded-2xl overflow-hidden shadow-lg border-4 border-white/20">
<iframe width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3192.5614755253276!2d3.0588235759999997!3d36.7372088!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x128fab4c1b7a7c19%3A0x1234567890abc!2sAlgiers%2C%20Algeria!5e0!3m2!1sen!2s!4v1234567890"></iframe>
</div>
</div>
<h3 class="text-2xl font-bold mb-4">Looking for More Options?</h3>
<p class="text-white/90 mb-2 text-sm font-light">Have questions about our other pricing plans and course options?</p>
<p class="text-white/80 text-xs mb-8 font-light">Get in touch with our team for personalized guidance and special offers.</p>
<a href="mailto:admin@speakly.com" class="contact-us-btn w-full px-8 py-3 bg-white text-primary font-bold rounded-2xl hover:bg-white/90 transition-all text-center">Contact Us Now</a>
<div class="mt-8 pt-8 border-t border-white/20 space-y-3 text-sm">
<div class="flex items-center justify-center gap-3 text-white/80">
<span class="material-symbols-outlined text-[20px]">mail</span>
<a href="mailto:admin@speakly.com" class="hover:text-white transition-colors">admin@speakly.com</a>
</div>
<div class="flex items-center justify-center gap-3 text-white/80">
<span class="material-symbols-outlined text-[20px]">phone</span>
<span>+213 345 464 654</span>
</div>
</div>
</div>

<!-- TCF Course -->
<div class="bg-slate-50 rounded-3xl p-8 border border-black/5 hover-lift flex flex-col">
<div class="mb-8">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-sm mb-4">
<span class="material-symbols-outlined text-[16px]">language</span>
TCF Français
</div>
<h3 class="text-2xl font-bold text-on-surface mb-2">TCF Certification</h3>
<p class="text-on-surface-variant text-sm font-light">Test de Connaissance du Français - Official French proficiency exam</p>
</div>
<div class="mb-8">
<div class="text-3xl font-bold text-on-surface">6,000 <span class="text-lg text-on-surface-variant">DA/course</span></div>
<p class="text-sm text-on-surface-variant mt-2">Available for all proficiency levels</p>
</div>
<div class="space-y-2 mb-8 flex-grow">
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">A1 - A2:</span>
<span class="text-on-surface-variant text-sm">Foundation & Elementary</span>
</div>
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">B1 - B2:</span>
<span class="text-on-surface-variant text-sm">Intermediate & Upper Intermediate</span>
</div>
<div class="flex items-center gap-3">
<span class="text-primary font-bold text-sm min-w-fit">C1 - C2:</span>
<span class="text-on-surface-variant text-sm">Advanced & Mastery</span>
</div>
</div>
<ul class="space-y-4 mb-8 flex-grow">
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">8-week focused preparation</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Official TCF practice materials</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Phonetics & pronunciation coaching</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl flex-shrink-0">check_circle</span>
<span class="text-on-surface-variant">Final exam simulation</span>
</li>
</ul>
</div>
</div>
</div>
</section>
<!-- CTA Section -->
<section class="py-24 px-8 bg-transparent relative overflow-hidden">
<div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary/40 to-transparent shadow-md shadow-primary/30"></div>
<!-- Educational Background Elements -->
<div class="absolute inset-0 opacity-[0.08]">
<span class="absolute top-[10%] left-[5%] material-symbols-outlined text-[120px] text-primary/30">menu_book</span>
<span class="absolute top-[15%] right-[8%] material-symbols-outlined text-[100px] text-primary/30">school</span>
<span class="absolute bottom-[20%] left-[10%] material-symbols-outlined text-[110px] text-primary/30">translate</span>
<span class="absolute bottom-[10%] right-[12%] material-symbols-outlined text-[130px] text-primary/30">psychology</span>
<span class="absolute top-[50%] left-[3%] material-symbols-outlined text-[90px] text-primary/30">record_voice_over</span>
<span class="absolute top-[40%] right-[5%] material-symbols-outlined text-[100px] text-primary/30">lightbulb</span>
</div>
<div class="max-w-4xl mx-auto text-center relative z-10 flex flex-col items-center">
<div class="flex items-center justify-center gap-8 w-full mb-10">
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
<h2 class="text-6xl md:text-[5.5rem] font-young-serif text-on-surface tracking-tight leading-[1.1] shrink-0">Ready to master a<br/><span class="text-purple italic">new language</span>?</h2>
<div class="h-[2px] bg-primary/40 shadow-md shadow-primary/40 flex-grow hidden md:block rounded-full"></div>
</div>
<p class="text-xl md:text-2xl text-on-surface-variant max-w-2xl mx-auto mb-12 leading-relaxed font-light">Join thousands of scholars who've transformed their linguistic future with Lumina Academy.</p>
<div class="flex flex-col md:flex-row gap-4 justify-center">
<a href="{{ url('/') }}#programs" class="px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:bg-primary/90 transition-colors">Explore Programs</a>
</div>
</div>
</section>
</main>
<footer class="bg-white/90 border-t border-primary/10 py-20 px-8" id="about">
<div class="max-w-7xl mx-auto">
<div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-20">
<div class="col-span-1 md:col-span-2">
<div class="text-3xl font-black-900 tracking-tight mb-8"><span class="text-on-surface">Lumina</span> <span class="text-purple">Academy</span></div>
<p class="text-on-surface-variant max-w-sm text-lg leading-relaxed font-light">Lumina Academy helps learners build real language confidence through structured classes, practical communication, and clear progression levels.</p>
</div>
<div>
<h4 class="font-black text-on-surface mb-6 uppercase tracking-wider text-sm">About</h4>
<ul class="space-y-4 text-on-surface-variant font-medium">
<li><a class="hover:text-primary transition-colors" href="{{ url('/') }}#programs">Language Programs</a></li>
<li><a class="hover:text-primary transition-colors" href="{{ url('/') }}#features">Student Portal Features</a></li>
<li><a class="hover:text-primary transition-colors" href="{{ url('/register-login?tab=register') }}">Enrollment</a></li>
<li><a class="hover:text-primary transition-colors" href="mailto:admin@speakly.com">Admissions Support</a></li>
</ul>
</div>
<div>
<h4 class="font-black text-on-surface mb-6 uppercase tracking-wider text-sm">Connect</h4>
<ul class="space-y-4 text-on-surface-variant font-medium">
<li><a class="hover:text-primary transition-colors" href="https://www.instagram.com/luminaacademy" rel="noopener noreferrer" target="_blank">Instagram</a></li>
<li><a class="hover:text-primary transition-colors" href="https://www.linkedin.com/company/lumina-academy" rel="noopener noreferrer" target="_blank">LinkedIn</a></li>
<li><a class="hover:text-primary transition-colors" href="https://maps.google.com/?q=Algiers,+Algeria" rel="noopener noreferrer" target="_blank">Google Maps</a></li>
<li><a class="hover:text-primary transition-colors" href="tel:+213345464654">+213 345 464 654</a></li>
</ul>
</div>
</div>
<div class="flex flex-col md:flex-row justify-between items-center pt-10 border-t border-primary/5 opacity-60">
<p class="text-sm font-medium">© 2026 Lumina Academy. All rights reserved.</p>
<div class="flex gap-8 text-sm font-medium mt-4 md:mt-0">
<a class="hover:underline" href="mailto:admin@speakly.com">Support</a>
<a class="hover:underline" href="https://maps.google.com/?q=Algiers,+Algeria" rel="noopener noreferrer" target="_blank">Algiers, Algeria</a>
<a class="hover:underline" href="tel:+213345464654">Call Us</a>
</div>
</div>
</div>
</footer>
<script id="languageDataJson" type="application/json">@json($languageModalData)</script>
<script>
// Language Details Data
const languageDataElement = document.getElementById('languageDataJson');
const languageData = languageDataElement
  ? JSON.parse(languageDataElement.textContent || '{}')
  : {};

document.addEventListener('DOMContentLoaded', function() {
  const slider = document.getElementById('programsSlider');
  const leftArrow = document.querySelector('.slider-arrow-left');
  const rightArrow = document.querySelector('.slider-arrow-right');
  const cards = document.querySelectorAll('.slider-card');
  
  let currentSlide = 0;
  const cardWidth = 384; // w-96 = 24rem = 384px
  const gap = 24;
  const cardsPerSlide = 3;
  const totalSlides = Math.ceil(cards.length / cardsPerSlide);
  
  let isDragging = false;
  let startX = 0;
  let currentX = 0;
  let dragThreshold = 50;
  
  function updateSliderPosition() {
    const translateAmount = currentSlide * (cardWidth + gap) * cardsPerSlide;
    slider.style.transform = `translateX(-${translateAmount}px)`;
  }
  
  function updateArrowStates() {
    if (currentSlide === 0) {
      leftArrow.style.opacity = '0.5';
      leftArrow.style.pointerEvents = 'none';
    } else {
      leftArrow.style.opacity = '1';
      leftArrow.style.pointerEvents = 'auto';
    }
    
    if (currentSlide === totalSlides - 1) {
      rightArrow.style.opacity = '0.5';
      rightArrow.style.pointerEvents = 'none';
    } else {
      rightArrow.style.opacity = '1';
      rightArrow.style.pointerEvents = 'auto';
    }
  }
  
  // Arrow click handlers
  leftArrow.addEventListener('click', function() {
    if (currentSlide > 0) {
      currentSlide--;
      updateSliderPosition();
      updateArrowStates();
    }
  });
  
  rightArrow.addEventListener('click', function() {
    if (currentSlide < totalSlides - 1) {
      currentSlide++;
      updateSliderPosition();
      updateArrowStates();
    }
  });
  
  // Drag functionality
  slider.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX;
    slider.style.cursor = 'grabbing';
  });
  
  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    currentX = e.clientX - startX;
  });
  
  document.addEventListener('mouseup', (e) => {
    if (!isDragging) return;
    isDragging = false;
    slider.style.cursor = 'grab';
    
    if (Math.abs(currentX) > dragThreshold) {
      if (currentX > 0 && currentSlide > 0) {
        currentSlide--;
      } else if (currentX < 0 && currentSlide < totalSlides - 1) {
        currentSlide++;
      }
      updateSliderPosition();
      updateArrowStates();
    }
    currentX = 0;
  });
  
  updateArrowStates();

  // Reviews Slider Logic
  const reviewsSlider = document.getElementById('reviewsSlider');
  const reviewsLeftArrow = document.querySelector('.reviews-arrow-left');
  const reviewsRightArrow = document.querySelector('.reviews-arrow-right');
  const reviewCards = document.querySelectorAll('.reviews-slider .review-card, .reviews-slider article');
  
  let currentReviewSlide = 0;
  // Based on w-96 = 384px + 24px gap
  const reviewCardWidth = 384; 
  const reviewGap = 24;
  const reviewCardsPerSlide = 3;
  const totalReviewSlides = Math.ceil(reviewCards.length / reviewCardsPerSlide);
  
  let isReviewDragging = false;
  let reviewStartX = 0;
  let currentReviewX = 0;
  
  function updateReviewSliderPosition() {
    const translateAmount = currentReviewSlide * (reviewCardWidth + reviewGap) * reviewCardsPerSlide;
    if (reviewsSlider) {
      reviewsSlider.style.transform = `translateX(-${translateAmount}px)`;
    }
  }
  
  function updateReviewArrowStates() {
    if (!reviewsLeftArrow || !reviewsRightArrow) return;
    
    if (currentReviewSlide === 0) {
      reviewsLeftArrow.style.opacity = '0.5';
      reviewsLeftArrow.style.pointerEvents = 'none';
    } else {
      reviewsLeftArrow.style.opacity = '1';
      reviewsLeftArrow.style.pointerEvents = 'auto';
    }
    
    if (currentReviewSlide >= totalReviewSlides - 1) {
      reviewsRightArrow.style.opacity = '0.5';
      reviewsRightArrow.style.pointerEvents = 'none';
    } else {
      reviewsRightArrow.style.opacity = '1';
      reviewsRightArrow.style.pointerEvents = 'auto';
    }
  }
  
  if (reviewsLeftArrow && reviewsRightArrow) {
    reviewsLeftArrow.addEventListener('click', function() {
      if (currentReviewSlide > 0) {
        currentReviewSlide--;
        updateReviewSliderPosition();
        updateReviewArrowStates();
      }
    });
    
    reviewsRightArrow.addEventListener('click', function() {
      if (currentReviewSlide < totalReviewSlides - 1) {
        currentReviewSlide++;
        updateReviewSliderPosition();
        updateReviewArrowStates();
      }
    });
  }

  if (reviewsSlider) {
    reviewsSlider.addEventListener('mousedown', (e) => {
      isReviewDragging = true;
      reviewStartX = e.clientX;
      reviewsSlider.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', (e) => {
      if (!isReviewDragging) return;
      currentReviewX = e.clientX - reviewStartX;
    });
    
    document.addEventListener('mouseup', (e) => {
      if (!isReviewDragging) return;
      isReviewDragging = false;
      reviewsSlider.style.cursor = 'grab';
      
      if (Math.abs(currentReviewX) > dragThreshold) {
        if (currentReviewX > 0 && currentReviewSlide > 0) {
          currentReviewSlide--;
        } else if (currentReviewX < 0 && currentReviewSlide < totalReviewSlides - 1) {
          currentReviewSlide++;
        }
        updateReviewSliderPosition();
        updateReviewArrowStates();
      }
      currentReviewX = 0;
    });
  }
  
  updateReviewArrowStates();

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const reviewVoteButtons = document.querySelectorAll('.review-vote-btn');

  reviewVoteButtons.forEach((button) => {
    button.addEventListener('click', async function (event) {
      event.preventDefault();

      if (this.disabled) {
        return;
      }

      const card = this.closest('[data-review-id]');
      const reviewId = card?.getAttribute('data-review-id');
      const vote = this.getAttribute('data-vote');

      if (!card || !reviewId || !vote) {
        return;
      }

      try {
        const response = await fetch(`/reviews/${reviewId}/vote`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ vote }),
        });

        const result = await response.json();

        if (!response.ok) {
          if (response.status === 409) {
            card.querySelectorAll('.review-vote-btn').forEach((btn) => {
              btn.disabled = true;
            });
          }
          return;
        }

        const ratingBadge = card.querySelector('.review-rating-value');
        if (ratingBadge && result.rating) {
          ratingBadge.textContent = `${result.rating}/5`;
        }

        card.querySelectorAll('.review-vote-btn').forEach((btn) => {
          btn.disabled = true;
        });
      } catch (error) {
      }
    });
  });
  
  // Modal functionality
  cards.forEach(card => {
    card.addEventListener('click', function(e) {
      // Only open modal if not dragging
      if (Math.abs(currentX) < 5) {
        const langCode = this.getAttribute('data-lang');
        openLanguageModal(langCode);
      }
    });
  });
  
  // Home button scroll to top
  const homeLink = document.querySelector('.nav-home-link');
  if (homeLink) {
    homeLink.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
  
});

function openLanguageModal(langCode) {
  const data = languageData[langCode];
  if (!data) return;
  
  const modal = document.createElement('div');
  modal.className = 'language-modal';
  modal.innerHTML = `
    <div class="modal-overlay"></div>
    <div class="modal-content">
      <button class="modal-close">×</button>
      <div class="modal-header">
        <h2>${data.title}</h2>
        <p class="modal-subtitle">${data.description}</p>
      </div>
      <div class="modal-body">
        <div class="modal-section">
          <h3>About This Program</h3>
          <p>${data.fullDescription}</p>
        </div>
        <div class="modal-section">
          <h3>Certified Exams & Pathways</h3>
          <div class="certifications-grid">
            ${data.certifications.map(cert => `
              <div class="certification-card">
                <h4>${cert.name}</h4>
                <ul>
                  ${cert.exams.map(exam => `<li>${exam}</li>`).join('')}
                </ul>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Close modal functionality
  const closeBtn = modal.querySelector('.modal-close');
  const overlay = modal.querySelector('.modal-overlay');
  
  closeBtn.addEventListener('click', () => modal.remove());
  overlay.addEventListener('click', () => modal.remove());
  
  // Close on escape key
  const escapeHandler = (e) => {
    if (e.key === 'Escape') {
      modal.remove();
      document.removeEventListener('keydown', escapeHandler);
    }
  };
  document.addEventListener('keydown', escapeHandler);
}
</script>
<style>
.language-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 32px;
  max-width: 700px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(40px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-close {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 40px;
  height: 40px;
  border: none;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 50%;
  font-size: 28px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  z-index: 1001;
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 40px 40px 20px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.modal-header h2 {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 12px 0;
  color: #1f1f1f;
}

.modal-subtitle {
  margin: 0;
  color: #666;
  font-size: 16px;
  line-height: 1.5;
}

.modal-body {
  padding: 40px;
}

.modal-section {
  margin-bottom: 32px;
}

.modal-section:last-child {
  margin-bottom: 0;
}

.modal-section h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 16px 0;
  color: #1f1f1f;
}

.modal-section p {
  margin: 0;
  color: #666;
  line-height: 1.7;
  font-size: 15px;
}

.certifications-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.certification-card {
  background: #f5f5f5;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.certification-card h4 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: #1f1f1f;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.certification-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.certification-card li {
  background: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  color: #333;
  border: 1px solid #ddd;
}
</style>
</body></html>
