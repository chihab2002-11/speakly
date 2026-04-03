<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted | Lumina Academy</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Young+Serif&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script>
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
                        "background": "#FBF8FF"
                    },
                    fontFamily: {
                        "inter": ["Inter", "sans-serif"],
                        "serif": ["Young Serif", "Georgia", "Cambria", "Times New Roman", "serif"],
                        "young-serif": ["Young Serif", "serif"]
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .hover-lift:hover { transform: translateY(-4px); }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col relative overflow-x-hidden">

    <!-- Background Patterns - Language & Learning Theme -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden w-full h-full select-none">
        
        <!-- Floating Language Characters -->
        <span class="absolute top-[8%] left-[5%] text-[120px] font-serif font-black text-primary/[0.06] rotate-12">A</span>
        <span class="absolute top-[12%] right-[8%] text-[100px] font-serif font-bold text-primary/[0.05] -rotate-12">あ</span>
        <span class="absolute bottom-[20%] left-[3%] text-[90px] font-serif font-bold text-primary/[0.06] -rotate-6">Ñ</span>
        <span class="absolute bottom-[15%] right-[5%] text-[140px] font-serif font-black text-primary/[0.07] rotate-[15deg]">¿</span>
        <span class="absolute top-[45%] right-[3%] text-[80px] font-serif font-bold text-primary/[0.05] -rotate-[15deg]">文</span>
        <span class="absolute top-[70%] left-[8%] text-[70px] font-serif font-bold text-primary/[0.05] rotate-[8deg]">ß</span>
        <span class="absolute top-[30%] left-[2%] text-[110px] font-serif font-black text-primary/[0.06] -rotate-[10deg]">Б</span>
        <span class="absolute bottom-[40%] right-[6%] text-[75px] font-serif font-bold text-primary/[0.05] rotate-[20deg]">α</span>
        <span class="absolute top-[5%] left-[40%] text-[60px] font-serif font-bold text-primary/[0.04] -rotate-[8deg]">É</span>
        <span class="absolute bottom-[8%] right-[35%] text-[85px] font-serif font-bold text-primary/[0.05] rotate-6">한</span>
        
        <!-- Academic Icons -->
        <span class="material-symbols-outlined absolute top-[20%] left-[10%] text-[100px] text-primary/[0.06] rotate-6">school</span>
        <span class="material-symbols-outlined absolute top-[15%] right-[15%] text-[120px] text-primary/[0.05] -rotate-12">menu_book</span>
        <span class="material-symbols-outlined absolute bottom-[25%] right-[12%] text-[140px] text-primary/[0.06] rotate-12">translate</span>
        <span class="material-symbols-outlined absolute top-[55%] left-[5%] text-[90px] text-primary/[0.05] rotate-[15deg]">record_voice_over</span>
        <span class="material-symbols-outlined absolute bottom-[10%] left-[20%] text-[110px] text-primary/[0.06] -rotate-12">public</span>
        <span class="material-symbols-outlined absolute top-[40%] right-[8%] text-[80px] text-primary/[0.05] -rotate-6">forum</span>
        <span class="material-symbols-outlined absolute bottom-[50%] left-[12%] text-[70px] text-primary/[0.04] rotate-[18deg]">edit_note</span>
        <span class="material-symbols-outlined absolute top-[75%] right-[20%] text-[95px] text-primary/[0.05] rotate-[22deg]">psychology</span>
        
        <!-- Geometric Shapes - Circles -->
        <div class="absolute top-[18%] right-[25%] w-32 h-32 border-[2px] border-primary/[0.06] rounded-full"></div>
        <div class="absolute bottom-[30%] left-[15%] w-24 h-24 border-[2px] border-primary/[0.05] rounded-full"></div>
        <div class="absolute top-[60%] right-[10%] w-20 h-20 border-[2px] border-primary/[0.04] rounded-full"></div>
        <div class="absolute bottom-[12%] right-[40%] w-28 h-28 border-[2px] border-primary/[0.05] rounded-full"></div>
        
        <!-- Geometric Shapes - Squares -->
        <div class="absolute top-[35%] left-[8%] w-20 h-20 border-[2px] border-primary/[0.05] rotate-45"></div>
        <div class="absolute bottom-[35%] right-[15%] w-28 h-28 border-[2px] border-primary/[0.06] rotate-12"></div>
        <div class="absolute top-[8%] right-[30%] w-16 h-16 border-[2px] border-primary/[0.04] -rotate-[15deg]"></div>
        
        <!-- Speech Bubbles -->
        <svg class="absolute top-[25%] right-[5%] w-16 h-16 text-primary/[0.06]" viewBox="0 0 100 100" fill="none">
            <path d="M10,10 h60 a10,10 0 0,1 10,10 v30 a10,10 0 0,1 -10,10 h-40 l-15,15 v-15 h-5 a10,10 0 0,1 -10,-10 v-30 a10,10 0 0,1 10,-10" stroke="currentColor" stroke-width="3" fill="none"/>
        </svg>
        <svg class="absolute bottom-[45%] left-[5%] w-14 h-14 text-primary/[0.05] rotate-12" viewBox="0 0 100 100" fill="none">
            <path d="M90,10 h-60 a10,10 0 0,0 -10,10 v30 a10,10 0 0,0 10,10 h40 l15,15 v-15 h5 a10,10 0 0,0 10,-10 v-30 a10,10 0 0,0 -10,-10" stroke="currentColor" stroke-width="3" fill="none"/>
        </svg>
        
        <!-- Book Icon -->
        <svg class="absolute top-[50%] right-[18%] w-20 h-20 text-primary/[0.05] -rotate-6" viewBox="0 0 100 100" fill="none">
            <rect x="15" y="10" width="70" height="80" rx="3" stroke="currentColor" stroke-width="3" fill="none"/>
            <line x1="50" y1="10" x2="50" y2="90" stroke="currentColor" stroke-width="2"/>
            <path d="M15,20 Q32,25 50,20" stroke="currentColor" stroke-width="1.5" fill="none"/>
            <path d="M50,20 Q68,25 85,20" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        
        <!-- Globe with meridians -->
        <svg class="absolute bottom-[20%] left-[25%] w-24 h-24 text-primary/[0.05]" viewBox="0 0 100 100" fill="none">
            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="2" fill="none"/>
            <ellipse cx="50" cy="50" rx="20" ry="40" stroke="currentColor" stroke-width="1.5" fill="none"/>
            <line x1="10" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="1.5"/>
            <path d="M15,30 Q50,35 85,30" stroke="currentColor" stroke-width="1" fill="none"/>
            <path d="M15,70 Q50,65 85,70" stroke="currentColor" stroke-width="1" fill="none"/>
        </svg>
        
        <!-- Graduation Cap -->
        <svg class="absolute top-[5%] right-[45%] w-20 h-20 text-primary/[0.06] rotate-[-10deg]" viewBox="0 0 100 100" fill="none">
            <polygon points="50,15 10,35 50,55 90,35" stroke="currentColor" stroke-width="2.5" fill="none"/>
            <path d="M25,40 v25 Q50,80 75,65 v-25" stroke="currentColor" stroke-width="2" fill="none"/>
            <line x1="85" y1="35" x2="85" y2="65" stroke="currentColor" stroke-width="2"/>
            <circle cx="85" cy="68" r="4" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        
        <!-- Decorative dots -->
        <div class="absolute top-[65%] left-[3%] grid grid-cols-3 gap-2 text-primary/[0.06]">
            <div class="w-2 h-2 rounded-full bg-current"></div>
            <div class="w-2 h-2 rounded-full bg-current"></div>
            <div class="w-2 h-2 rounded-full bg-current"></div>
            <div class="w-2 h-2 rounded-full bg-current"></div>
            <div class="w-2 h-2 rounded-full bg-current"></div>
            <div class="w-2 h-2 rounded-full bg-current"></div>
        </div>
        
        <!-- Plus signs -->
        <span class="absolute top-[80%] right-[8%] text-[40px] font-light text-primary/[0.05]">+</span>
        <span class="absolute top-[10%] left-[20%] text-[30px] font-light text-primary/[0.04]">+</span>
        <span class="absolute bottom-[5%] left-[50%] text-[35px] font-light text-primary/[0.04]">+</span>
        
        <!-- Star shapes -->
        <svg class="absolute bottom-[60%] right-[3%] w-10 h-10 text-primary/[0.05]" viewBox="0 0 50 50" fill="none">
            <polygon points="25,5 30,20 45,20 33,30 38,45 25,35 12,45 17,30 5,20 20,20" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        
    </div>

    <!-- Navigation Header -->
    <nav class="w-full z-50 glass-nav border-b border-primary/5 bg-white/80 relative">
        <div class="flex justify-between items-center px-8 py-4 max-w-7xl mx-auto">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                    </svg>
                </div>
                <div class="text-2xl font-young-serif tracking-tight">
                    <span class="text-primary">Lumina</span> <span class="text-purple">Academy</span>
                </div>
            </a>
            
            <!-- Logout Button -->
            <div class="flex items-center">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="border border-primary text-primary px-7 py-2.5 rounded-full font-bold hover:bg-primary/5 transition-all hover-lift">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-8 py-16 relative z-10">
        <div class="max-w-2xl w-full text-center">
            
            <!-- Success Checkmark Icon -->
            <div class="mb-8 flex justify-center">
                <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center">
                    <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Heading -->
            <h1 class="text-4xl md:text-5xl font-young-serif text-on-surface tracking-tight mb-6" style="font-weight: 800;">
                Application Submitted
            </h1>
            
            <!-- Description -->
            <p class="text-lg md:text-xl text-on-surface-variant leading-relaxed mb-10 max-w-xl mx-auto">
                Thank you for registering with Lumina Academy. Your application is currently under review by our administration team. You will receive an email notification once your account has been approved.
            </p>
            
            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                <a href="mailto:support@luminaacademy.com" 
                   class="text-primary font-bold text-lg hover:underline transition-all flex items-center gap-2">
                    Contact Support
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </a>
            </div>
            
            <!-- Info Grid -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-sm border border-primary/5 p-8 max-w-lg mx-auto">
                <div class="grid grid-cols-3 gap-6 text-center">
                    <!-- Submitted Date -->
                    <div>
                        <div class="text-on-surface-variant text-sm font-medium mb-2">Submitted</div>
                        <div class="text-on-surface font-bold text-lg">{{ now()->format('M d, Y') }}</div>
                    </div>
                    
                    <!-- Review Time -->
                    <div class="border-x border-primary/10 px-4">
                        <div class="text-on-surface-variant text-sm font-medium mb-2">Review Time</div>
                        <div class="text-on-surface font-bold text-lg">1-2 Days</div>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <div class="text-on-surface-variant text-sm font-medium mb-2">Status</div>
                        <div class="inline-flex items-center gap-2">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                            <span class="text-on-surface font-bold text-lg">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white/90 border-t border-primary/10 py-8 px-8 relative z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-on-surface-variant font-medium">
                © {{ date('Y') }} Lumina Academy. All rights reserved.
            </p>
            <div class="flex gap-6 text-sm text-on-surface-variant font-medium">
                <a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
                <a class="hover:text-primary transition-colors" href="#">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
