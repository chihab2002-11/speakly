<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Lumina Academy</title>
    
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
        
        <!-- Academic Icons -->
        <span class="material-symbols-outlined absolute top-[20%] left-[10%] text-[100px] text-primary/[0.06] rotate-6">school</span>
        <span class="material-symbols-outlined absolute top-[15%] right-[15%] text-[120px] text-primary/[0.05] -rotate-12">menu_book</span>
        <span class="material-symbols-outlined absolute bottom-[25%] right-[12%] text-[140px] text-primary/[0.06] rotate-12">translate</span>
        <span class="material-symbols-outlined absolute top-[55%] left-[5%] text-[90px] text-primary/[0.05] rotate-[15deg]">lock_reset</span>
        <span class="material-symbols-outlined absolute bottom-[10%] left-[20%] text-[110px] text-primary/[0.06] -rotate-12">mail</span>
        <span class="material-symbols-outlined absolute top-[40%] right-[8%] text-[80px] text-primary/[0.05] -rotate-6">key</span>
        
        <!-- Geometric Shapes - Circles -->
        <div class="absolute top-[18%] right-[25%] w-32 h-32 border-[2px] border-primary/[0.06] rounded-full"></div>
        <div class="absolute bottom-[30%] left-[15%] w-24 h-24 border-[2px] border-primary/[0.05] rounded-full"></div>
        <div class="absolute top-[60%] right-[10%] w-20 h-20 border-[2px] border-primary/[0.04] rounded-full"></div>
        
        <!-- Geometric Shapes - Squares -->
        <div class="absolute top-[35%] left-[8%] w-20 h-20 border-[2px] border-primary/[0.05] rotate-45"></div>
        <div class="absolute bottom-[35%] right-[15%] w-28 h-28 border-[2px] border-primary/[0.06] rotate-12"></div>
        
        <!-- Globe -->
        <svg class="absolute bottom-[20%] left-[25%] w-24 h-24 text-primary/[0.05]" viewBox="0 0 100 100" fill="none">
            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="2" fill="none"/>
            <ellipse cx="50" cy="50" rx="20" ry="40" stroke="currentColor" stroke-width="1.5" fill="none"/>
            <line x1="10" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        
        <!-- Plus signs -->
        <span class="absolute top-[80%] right-[8%] text-[40px] font-light text-primary/[0.05]">+</span>
        <span class="absolute top-[10%] left-[20%] text-[30px] font-light text-primary/[0.04]">+</span>
        
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
            
            <!-- Login Button -->
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" 
                   class="border border-primary text-primary px-7 py-2.5 rounded-full font-bold hover:bg-primary/5 transition-all hover-lift">
                    Login
                </a>
                <a href="{{ route('register') }}" 
                   class="bg-primary text-white px-7 py-2.5 rounded-full font-bold shadow-md hover:shadow-lg hover:opacity-95 transition-all hover-lift">
                    Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-8 py-16 relative z-10">
        <div class="w-full max-w-md">
            
            <!-- Card Container -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-xl border border-primary/5 p-10">
                
                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[40px]">lock_reset</span>
                    </div>
                </div>
                
                <!-- Heading -->
                <h1 class="text-3xl font-young-serif text-on-surface tracking-tight mb-3 text-center">
                    Forgot Password?
                </h1>
                
                <!-- Description -->
                <p class="text-on-surface-variant text-center mb-8 leading-relaxed">
                    No worries! Enter your email address and we'll send you a link to reset your password.
                </p>
                
                <!-- Session Status (Success Message) -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-surface-container-low rounded-xl border border-primary/20 text-primary text-sm text-center font-medium">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 rounded-xl border border-red-200 text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Form -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-on-surface mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">mail</span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                required 
                                autofocus
                                placeholder="Enter your email"
                                class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-on-surface-variant/50"
                            />
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-primary/20 hover:shadow-xl hover:opacity-95 transition-all hover-lift flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[20px]">send</span>
                        Send Reset Link
                    </button>
                </form>
                
                <!-- Back to Login -->
                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}" class="text-primary font-bold hover:underline transition-all inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back to Login
                    </a>
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
