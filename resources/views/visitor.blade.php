<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
<div class="absolute -left-20 top-20 text-[600px] font-black-900 text-primary/[0.03] select-none leading-none">L</div>
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
<div class="flex flex-wrap gap-6 items-center">
<a href="/register-login?tab=register" class="bg-primary text-white px-12 py-5 rounded-full font-black text-lg shadow-xl shadow-primary/20 hover-lift active:scale-95 transition-all">Join Our School</a>
<a class="flex items-center gap-3 font-bold text-on-surface hover:text-primary transition-colors group" href="#about">
                        View Curriculum 
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
</div>
</div>
<div class="relative">
<div class="relative z-10 aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl transform lg:rotate-3 hover:rotate-0 transition-transform duration-700">
<img alt="Collaborative learning in a modern environment" 
     class="w-full h-full object-cover" 
     src="{{ asset('images/learners.jpg') }}"/></div>
<div class="absolute -bottom-10 -left-10 z-20 bg-white p-6 rounded-2xl shadow-2xl max-w-[280px] hover-lift">
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
<section class="py-32 px-8 bg-surface-container-low/80 relative" id="programs">
<!-- Subtle Flowing Shape Background -->
<div class="absolute top-0 right-0 w-1/2 h-full opacity-[0.03] pointer-events-none">
<svg class="w-full h-full" viewbox="0 0 500 500">
<path d="M444.5,296.5Q419,343,382,374.5Q345,406,297.5,427.5Q250,449,195.5,435Q141,421,114.5,373.5Q88,326,71,274.5Q54,223,79.5,173.5Q105,124,153,95.5Q201,67,253.5,74.5Q306,82,354.5,108Q403,134,436.5,185.5Q470,237,444.5,296.5Z" fill="#2D8C5E"></path>
</svg>
</div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="mb-24 flex flex-col md:flex-row md:items-end justify-between gap-8">
<div class="space-y-4">
<h2 class="text-5xl md:text-6xl font-young-serif text-on-surface tracking-tightest leading-tight">World-Class <br/>Programs.</h2>
<p class="text-on-surface-variant text-xl leading-relaxed max-w-xl font-light">Curated pathways for every ambition, from conversational ease to professional certification, delivered by industry experts.</p>
</div>
<div class="hidden md:flex gap-4">
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
<!-- English -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="en">
<div class="mb-10 relative">
<img alt="UK Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB9fBXUXgMsr-Hhbu0-aE7d4oaCk8ACtNt3np_KPuv_OvjhRU33T2DwBLlUS5T4ytw74AbIiSlGqtj4xsxGIX52mjGe6FH5GI_td1YhpVBwyP2aDpF0MuG_UpkyjDr_gjy1KbWqP1ScNZEXJexrIpo-zj59Wpe8A2Tp9ig6NWIUQYWJJVZEL5E6RxCi0HZ2_9unSrLLk6rox-VPt7V8jbt_G7WbaDNKa6vT8YFsRt4XyyJd9MMMFwGwoRocyIDE3-J3k46SmjHQcBve"/>
<div class="absolute -top-2 -right-2 text-en-red font-black-900 text-2xl">EN</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">English Mastery</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Cambridge preparation and business-level fluency. Our most expansive program.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- Spanish -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="es">
<div class="mb-10 relative">
<img alt="Spanish Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuACTBwMnOv7Pj2HZWIkBQiVbRR6BHwN0aktcIDkkPobYVYC_FDRxF7PY83FHD6xyZ2Fa83QSYqnvtbnE1lGOGVh9Q0YoBndVCtBgqUTl8JJ5uOxclJrNQIUHI8mGrVe8YxsZ0ehd99hGGNpz5pBUBh7TzEWcmKmGdTSOOTZllFSd99W0kJ2rb5K0JU-LW60Kgi9_Y69RBYXhOou-S3_J3xiiQ9J3lXJOCZJ_3-IyrtT4-1_rfZtkGbuaOZXppeI7msVWTpS6M-JhQId"/>
<div class="absolute -top-2 -right-2 text-es-orange font-black-900 text-2xl">ES</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">Spanish Immersion</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Deep dive into Castilian and Latin cultures through DELE-aligned courses.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- French -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="fr">
<div class="mb-10 relative">
<img alt="French Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnpwuOKXKvsaO5dDtKhu7VC-_rsToQbUWKgSSRNfR-CfyT37MXF2s3Z1DSJas5RGRAUZXMRg6LJzVXH8wqzqoG06hf_Qd4hCcgR7R7yeeCuBkQ3RRe_ZlpGWFyoeH_bYV2gPzvu_nRxMldUY_jTr5fHZSD7L__Nj3ARkLqlWD1DqP3mHoF7KvE-UMbTyCve0MbFOBoM67czJgFj2HGUzMjdCcIlgznMm_zFtmCwfOl-k7TCMQ5saMhmE-j-zEzAzAV2pIRZ-ZzKQQi"/>
<div class="absolute -top-2 -right-2 text-fr-blue font-black-900 text-2xl">FR</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">French Elegance</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Master the language of diplomacy and art with DALF-certified instructors.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- German -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="de">
<div class="mb-10 relative">
<img alt="German Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="{{ asset('images/flag_gr.png') }}"/>
<div class="absolute -top-2 -right-2 text-primary font-black-900 text-2xl">DE</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">German Precision</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Focus on technical German and Goëthe-Zertifikat certification pathways.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- Italian -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="it">
<div class="mb-10 relative">
<img alt="Italian Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="{{ asset('images/flag_it.png') }}"/>
<div class="absolute -top-2 -right-2 text-primary font-black-900 text-2xl">IT</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">Italian Heritage</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Explore the linguistic roots of the Renaissance and culinary traditions.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- Portuguese -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="pt">
<div class="mb-10 relative">
<img alt="Portuguese Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8rL7E9M0i5DJh8t4n_xOhJ5O9zqaB9Z_N5nF3M9KqZpWv6EY3E7C_7bQ_8QhHkXQpN8ZJvTZqNQcvyIVnKPm_zYqYYn3h8kKf8C8qG9nG_CrHX3E0gLnhxWpFJ6K1vU7HQz_HjPxvJSqEpJrVBQF9K4ePp5N6Y"/>
<div class="absolute -top-2 -right-2 text-primary font-black-900 text-2xl">PT</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">Portuguese Power</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Connect with 250+ million speakers worldwide through CAPLE-aligned methodologies.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>

<!-- Japanese -->
<div class="slider-card bg-white p-8 rounded-3xl shadow-sm border border-black/[0.03] flex flex-col min-h-[360px] hover-lift group flex-shrink-0 w-96 cursor-pointer" data-lang="jp">
<div class="mb-10 relative">
<img alt="Japanese Flag" class="w-16 h-16 rounded-full object-cover border-2 border-primary/10 shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDJY4Z7q4m8qKlJ3qJ0zH8m9Pp7vQ5qKzQ8pR9tS0uVwXyZ2aAbCdEfGhIjK4L9nM5oP6qRsTuVuWxYzB5nKpQrStVwXyZ2aAbCdEfGhIjK4L9nM5oP6qRs"/>
<div class="absolute -top-2 -right-2 text-primary font-black-900 text-2xl">JP</div>
</div>
<h3 class="text-xl font-bold text-on-surface mb-4">Japanese Mastery</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-auto font-medium">Master Hiragana, Katakana, and Kanji with JLPT certification pathways included.</p>
<div class="text-primary font-bold flex items-center gap-2 mt-8 cursor-pointer group-hover:gap-3 transition-all">
Explore <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Features/Portal Section -->
<section class="py-32 px-8 relative overflow-hidden" id="features">
<div class="absolute inset-0 bg-grid opacity-[0.02] pointer-events-none"></div>
<div class="max-w-7xl mx-auto relative z-10">
<div class="text-center mb-24 space-y-6">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Portal Benefits</span>
<h2 class="text-5xl md:text-6xl font-young-serif tracking-header text-on-surface">Tools for the Modern Scholar.</h2>
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
<section class="py-32 px-8 bg-white relative overflow-hidden" id="testimonials">
<div class="max-w-7xl mx-auto">
<div class="text-center mb-24 space-y-6">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Student Success</span>
<h2 class="text-5xl md:text-6xl font-young-serif tracking-header text-on-surface">Voices of Lumina.</h2>
<p class="text-on-surface-variant text-xl leading-relaxed max-w-2xl mx-auto font-light">Hear from our scholars who've transformed their linguistic journeys at Lumina Academy.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Testimonial 1 -->
<div class="bg-surface-container-low p-8 rounded-3xl border border-black/5 hover-lift">
<div class="flex items-center gap-1 mb-4">
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
</div>
<p class="text-on-surface-variant text-lg leading-relaxed mb-6 font-light italic">"I went from beginner to Cambridge Advanced in just 18 months. The personalized curriculum and expert instructors made all the difference."</p>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-purple"></div>
<div>
<p class="font-black-900 text-on-surface">Sarah Mitchell</p>
<p class="text-sm text-on-surface-variant">Cambridge CAE Certified</p>
</div>
</div>
</div>
<!-- Testimonial 2 -->
<div class="bg-surface-container-low p-8 rounded-3xl border border-black/5 hover-lift">
<div class="flex items-center gap-1 mb-4">
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
</div>
<p class="text-on-surface-variant text-lg leading-relaxed mb-6 font-light italic">"The cultural immersion aspect of the Spanish program helped me understand not just the language, but the people and traditions behind it."</p>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple to-primary"></div>
<div>
<p class="font-black-900 text-on-surface">Carlos Rodríguez</p>
<p class="text-sm text-on-surface-variant">DELE B2 Certified</p>
</div>
</div>
</div>
<!-- Testimonial 3 -->
<div class="bg-surface-container-low p-8 rounded-3xl border border-black/5 hover-lift">
<div class="flex items-center gap-1 mb-4">
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
<span class="material-symbols-outlined text-primary text-2xl">star</span>
</div>
<p class="text-on-surface-variant text-lg leading-relaxed mb-6 font-light italic">"Lumina's cognitive progress tracking showed me exactly where I was improving, keeping me motivated throughout my learning journey."</p>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan to-primary"></div>
<div>
<p class="font-black-900 text-on-surface">Yuki Tanaka</p>
<p class="text-sm text-on-surface-variant">JLPT N1 Certified</p>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Pricing Section -->
<section class="py-32 px-8 bg-surface-container-low/50 relative overflow-hidden" id="pricing">
<div class="max-w-7xl mx-auto">
<div class="text-center mb-24 space-y-6">
<span class="inline-block px-4 py-1.5 rounded-full bg-primary/5 text-primary font-black tracking-widest uppercase text-xs border border-primary/10">Flexible Plans</span>
<h2 class="text-5xl md:text-6xl font-young-serif tracking-header text-on-surface">Investment in Your Future.</h2>
<p class="text-on-surface-variant text-xl leading-relaxed max-w-2xl mx-auto font-light">Choose the language course that best fits your learning goals and linguistic ambitions.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
<!-- IELTS Course -->
<div class="bg-white rounded-3xl p-8 border border-black/5 hover-lift flex flex-col">
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
<div class="bg-gradient-to-br from-primary to-primary/80 rounded-3xl p-8 flex flex-col justify-center items-center text-center text-white hover-lift">
<div class="mb-6">
<span class="material-symbols-outlined text-6xl text-white/80">contact_support</span>
</div>
<h3 class="text-2xl font-bold mb-4">Looking for More Options?</h3>
<p class="text-white/90 mb-2 text-sm font-light">Have questions about our other pricing plans and course options?</p>
<p class="text-white/80 text-xs mb-8 font-light">Get in touch with our team for personalized guidance and special offers.</p>
<button class="contact-us-btn w-full px-8 py-3 bg-white text-primary font-bold rounded-2xl hover:bg-white/90 transition-all">Contact Us Now</button>
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
<div class="bg-white rounded-3xl p-8 border border-black/5 hover-lift flex flex-col">
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
<!-- Educational Background Elements -->
<div class="absolute inset-0 opacity-[0.08]">
<span class="absolute top-[10%] left-[5%] material-symbols-outlined text-[120px] text-primary/30">menu_book</span>
<span class="absolute top-[15%] right-[8%] material-symbols-outlined text-[100px] text-primary/30">school</span>
<span class="absolute bottom-[20%] left-[10%] material-symbols-outlined text-[110px] text-primary/30">translate</span>
<span class="absolute bottom-[10%] right-[12%] material-symbols-outlined text-[130px] text-primary/30">psychology</span>
<span class="absolute top-[50%] left-[3%] material-symbols-outlined text-[90px] text-primary/30">record_voice_over</span>
<span class="absolute top-[40%] right-[5%] material-symbols-outlined text-[100px] text-primary/30">lightbulb</span>
</div>
<div class="max-w-4xl mx-auto text-center relative z-10">
<h2 class="text-5xl md:text-6xl font-young-serif text-on-surface tracking-tightest mb-6 leading-tight">Ready to Master a New Language?</h2>
<p class="text-on-surface-variant text-xl leading-relaxed mb-12 font-light">Join thousands of scholars who've transformed their linguistic future with Lumina Academy.</p>
<div class="flex flex-col md:flex-row gap-4 justify-center">
<a href="{{ url('/') }}#programs" class="px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:bg-primary/90 transition-colors">Explore Programs</a>
</div>
</div>
</section>
</main>
<footer class="bg-white/90 border-t border-primary/10 py-20 px-8">
<div class="max-w-7xl mx-auto">
<div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-20">
<div class="col-span-1 md:col-span-2">
<div class="text-3xl font-black-900 tracking-tight mb-8"><span class="text-on-surface">Lumina</span> <span class="text-purple">Academy</span></div>
<p class="text-on-surface-variant max-w-sm text-lg leading-relaxed font-light">The premier institute for language mastery, where academic tradition meets modern pedagogical innovation.</p>
</div>
<div>
<h4 class="font-black text-on-surface mb-6 uppercase tracking-wider text-sm">Institution</h4>
<ul class="space-y-4 text-on-surface-variant font-medium">
<li><a class="hover:text-primary transition-colors" href="#">Our Ethos</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Curriculum</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Faculty</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Admissions</a></li>
</ul>
</div>
<div>
<h4 class="font-black text-on-surface mb-6 uppercase tracking-wider text-sm">Connect</h4>
<ul class="space-y-4 text-on-surface-variant font-medium">
<li><a class="hover:text-primary transition-colors" href="#">Instagram</a></li>
<li><a class="hover:text-primary transition-colors" href="#">LinkedIn</a></li>
<li><a class="hover:text-primary transition-colors" href="#">The Gallery Journal</a></li>
</ul>
</div>
</div>
<div class="flex flex-col md:flex-row justify-between items-center pt-10 border-t border-primary/5 opacity-60">
<p class="text-sm font-medium">© 2026 Lumina Academy. All rights reserved.</p>
<div class="flex gap-8 text-sm font-medium mt-4 md:mt-0">
<a class="hover:underline" href="#">Privacy Policy</a>
<a class="hover:underline" href="#">Terms of Service</a>
<a class="hover:underline" href="#">Cookies</a>
</div>
</div>
</div>
</footer>
<script>
// Language Details Data
const languageData = {
  en: {
    name: 'English',
    title: 'English Mastery',
    description: 'Cambridge preparation and business-level fluency. Our most expansive program.',
    fullDescription: 'Master the English language with our comprehensive programs designed for learners of all levels. From beginner fundamentals to advanced business communication, our expert instructors guide you through Cambridge exam preparation, TOEFL/IELTS readiness, and professional English mastery.',
    certifications: [
      { name: 'Cambridge ESOL', exams: ['KET', 'PET', 'FCE', 'CAE', 'CPE'] },
      { name: 'IELTS', exams: ['Academic', 'General Training'] },
      { name: 'TOEFL', exams: ['iBT'] },
      { name: 'Business English', exams: ['BEC Preliminary', 'BEC Vantage', 'BEC Higher'] }
    ]
  },
  es: {
    name: 'Spanish',
    title: 'Spanish Immersion',
    description: 'Deep dive into Castilian and Latin cultures through DELE-aligned courses.',
    fullDescription: 'Immerse yourself in the Spanish language and rich Hispanic cultures. Our programs combine linguistic excellence with cultural exploration, preparing you for everything from casual conversations to professional environments across Spain and Latin America.',
    certifications: [
      { name: 'DELE', exams: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] },
      { name: 'SIELE', exams: ['SIELE Global'] },
      { name: 'Cervantes Institute', exams: ['Official Certification'] }
    ]
  },
  fr: {
    name: 'French',
    title: 'French Elegance',
    description: 'Master the language of diplomacy and art with DALF-certified instructors.',
    fullDescription: 'Discover the elegance and sophistication of the French language. Our courses prepare you for successful communication in diplomatic, cultural, and professional contexts, with expert guidance from certified DALF instructors.',
    certifications: [
      { name: 'DELF', exams: ['A1', 'A2', 'B1', 'B2'] },
      { name: 'DALF', exams: ['C1', 'C2'] },
      { name: 'TCF', exams: ['TCF Tout Public', 'TCF DAP'] }
    ]
  },
  de: {
    name: 'German',
    title: 'German Precision',
    description: 'Focus on technical German and Goëthe-Zertifikat certification pathways.',
    fullDescription: 'Master the precision and structure of the German language. Whether for academic pursuits, technical communication, or professional advancement, our programs equip you with the linguistic tools needed to excel in German-speaking environments.',
    certifications: [
      { name: 'Goethe Zertifikat', exams: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] },
      { name: 'TestDaF', exams: ['TestDaF (Academic)'] },
      { name: 'ZD', exams: ['Zertifikat Deutsch'] }
    ]
  },
  it: {
    name: 'Italian',
    title: 'Italian Heritage',
    description: 'Explore the linguistic roots of the Renaissance and culinary traditions.',
    fullDescription: 'Connect with Italian culture, art, and tradition through language mastery. From opera and literature to culinary arts and modern business communication, explore the richness of Italian across all proficiency levels.',
    certifications: [
      { name: 'CELI', exams: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] },
      { name: 'PLIDA', exams: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] },
      { name: 'AIL', exams: ['Italian Language Certificate'] }
    ]
  },
  pt: {
    name: 'Portuguese',
    title: 'Portuguese Power',
    description: 'Connect with 250+ million speakers worldwide through CAPLE-aligned methodologies.',
    fullDescription: 'Access the world of Portuguese, spoken by over 250 million people globally. Our programs cover both European and Brazilian variants, preparing you to communicate effectively across continents.',
    certifications: [
      { name: 'CAPLE', exams: ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] },
      { name: 'CELPE-BRAS', exams: ['Brazilian Portuguese'] },
      { name: 'DEPLE', exams: ['European Portuguese'] }
    ]
  },
  jp: {
    name: 'Japanese',
    title: 'Japanese Mastery',
    description: 'Master Hiragana, Katakana, and Kanji with JLPT certification pathways included.',
    fullDescription: 'Master the intricacies of the Japanese language, from fundamental writing systems to complex cultural communication. Our structured approach ensures steady progression through each proficiency level with cultural context.',
    certifications: [
      { name: 'JLPT', exams: ['N1', 'N2', 'N3', 'N4', 'N5'] },
      { name: 'J.TEST', exams: ['J.TEST for Japanese'] },
      { name: 'Kanji Kentei', exams: ['Kanji Proficiency Test'] }
    ]
  }
};

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
  
  // Contact Us button
  const contactBtn = document.querySelector('.contact-us-btn');
  if (contactBtn) {
    contactBtn.addEventListener('click', function(e) {
      e.preventDefault();
      alert('📧 admin@speakly.com\n📱 +213 345 464 654');
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
</script>
</body></html>