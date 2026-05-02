<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | Lumina Academy</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Young+Serif&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#2D8C5E",
                        "purple": "#5B4ED4",
                        "on-primary": "#ffffff",
                        "surface": "#ffffff",
                        "on-surface": "#1a1b22",
                        "on-surface-variant": "#444653",
                        "surface-container-low": "#E8F9F1",
                        "surface-container-high": "#A8E6C5",
                    },
                    fontFamily: {
                        "inter": ["Inter", "sans-serif"],
                        "young-serif": ["Young Serif", "serif"]
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
        
        /* Form transition styles */
        .form-panel {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .form-panel.active {
            opacity: 1;
            transform: translateX(0);
        }
        .form-panel.inactive {
            opacity: 0;
            transform: translateX(20px);
            position: absolute;
            pointer-events: none;
        }
        .forms-container {
            position: relative;
        }
        
        /* Custom radio button styling */
        .role-option {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .role-option:hover {
            border-color: rgba(45, 140, 94, 0.5);
            background-color: rgba(45, 140, 94, 0.05);
        }
        .role-option.selected {
            border-color: #2D8C5E;
            background-color: rgba(45, 140, 94, 0.1);
            box-shadow: 0 0 0 2px rgba(45, 140, 94, 0.2);
        }
        .role-option.selected .role-text {
            color: #2D8C5E;
            font-weight: 600;
        }
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

    </style>
</head>
<body class="bg-white text-on-surface min-h-screen flex flex-col" data-open-register="{{ old('requested_role') || request()->query('tab') === 'register' ? '1' : '0' }}">

    <!-- Navigation -->
    @include('partials.navigation', ['fixed' => false])

    <!-- Main Content -->
    <main class="flex-1 flex lg:overflow-hidden">
        <!-- Left Side - Decorative Section -->
        <div class="hidden lg:flex lg:w-[45%] bg-surface-container-low relative overflow-hidden flex-col p-12 gap-6">
            <!-- Decorative curved shape at top-left -->
            <div class="absolute top-0 left-0 w-64 h-64">
                <svg viewBox="0 0 200 200" class="w-full h-full">
                    <path d="M0,0 Q0,100 100,100 Q200,100 200,200 L0,200 Z" fill="#c8e6d5" opacity="0.5"/>
                </svg>
            </div>
            
            <!-- Decorative curved shape at bottom-right -->
            <div class="absolute bottom-0 right-0 w-96 h-96">
                <svg viewBox="0 0 300 300" class="w-full h-full">
                    <path d="M300,300 Q300,150 150,150 Q0,150 0,0 L300,0 Z" fill="#c8e6d5" opacity="0.4"/>
                </svg>
            </div>
            
            <!-- Content -->
            <div class="relative z-10">
                <!-- Tag -->
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/60 text-primary font-semibold text-sm mb-8 border border-primary/10">
                    THE COGNITIVE GALLERY
                </div>
                
                <!-- Heading -->
                <h1 class="text-5xl font-young-serif text-on-surface leading-tight mb-6">
                    <span>Every great</span><br/>
                    <span class="text-purple">journey</span><br/>
                    <span>starts </span><span class="text-purple">here.</span>
                </h1>
                
                <!-- Subtext -->
                <p class="text-on-surface-variant text-lg max-w-md leading-relaxed">
                    Continue your journey in our academic ecosystem. Join thousands of scholars mastering the world's most beautiful languages.
                </p>
            </div>
            
                <!-- Trust Cards -->
                <div class="relative z-10 space-y-3 max-w-sm">
                    <div class="bg-white/90 rounded-xl p-3.5 shadow-sm border border-white/70 flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-blue-600">verified_user</span>
                        </div>
                        <div>
                            <div class="text-base font-semibold text-on-surface leading-tight">Secure approval flow</div>
                            <div class="text-xs text-on-surface-variant mt-1">Role-based onboarding for students, parents, teachers, and secretaries.</div>
                        </div>
                    </div>
                    
                    <div class="bg-white/90 rounded-xl p-3.5 shadow-sm border border-white/70 flex items-start gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-emerald-700">support_agent</span>
                        </div>
                        <div>
                            <div class="text-base font-semibold text-on-surface leading-tight">Dedicated support</div>
                            <div class="text-xs text-on-surface-variant mt-1">Need help? Contact our team directly from the portal.</div>
                        </div>
                    </div>
                </div>
            
            <!-- Quote -->
            <div class="relative z-10 bg-white/40 backdrop-blur-sm border border-white/60 rounded-2xl p-5 shadow-sm max-w-sm">
                <p class="text-on-surface-variant italic text-base leading-relaxed">
                    "Education is the kindling of a flame, not the filling of a vessel."
                </p>
                <p class="text-primary font-semibold text-sm mt-2">— SOCRATES</p>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 lg:p-16 lg:h-full lg:items-start lg:overflow-y-auto">
            <div class="w-full max-w-md lg:pt-2">
                <!-- Tabs -->
                <div class="flex mb-8 bg-gray-100 rounded-full p-1">
                    <button id="signInTab" onclick="showSignIn()" class="flex-1 py-3 px-6 bg-primary text-white rounded-full font-semibold shadow-sm transition-all duration-300">
                        Sign In
                    </button>
                    <button id="createAccountTab" onclick="showCreateAccount()" class="flex-1 py-3 px-6 text-on-surface-variant font-medium rounded-full hover:text-on-surface transition-all duration-300">
                        Create Account
                    </button>
                </div>
                
                <!-- Forms Container -->
                <div class="forms-container">
                    <!-- Sign In Form -->
                    <div id="signInForm" class="form-panel active">
                    <!-- Welcome Text -->
                    <h2 class="text-3xl font-young-serif text-on-surface mb-2">Welcome Back</h2>
                    <p class="text-on-surface-variant mb-8">Please enter your details to access your portal.</p>
                    
                    <!-- Social Login Buttons -->
                    <div class="flex gap-4 mb-6">
                        <a href="https://accounts.google.com/" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-3 py-3 px-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="font-medium text-on-surface">Google</span>
                        </a>
                        <a href="https://www.linkedin.com/company/lumina-academy" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-3 py-3 px-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#0A66C2">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            <span class="font-medium text-on-surface">LinkedIn</span>
                        </a>
                    </div>
                    
                    <!-- Divider -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-primary font-medium text-sm">OR EMAIL</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>
                    
                    <!-- Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Global Error Messages -->
                        @if ($errors->any() && !session('register_errors'))
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">EMAIL ADDRESS</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input 
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    placeholder="name@lumina.edu"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide">PASSWORD</label>
                                <a href="{{ route('password.request') }}" class="text-primary font-semibold text-sm hover:underline">Forgot your password?</a>
                            </div>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input 
                                    id="loginPassword"
                                    type="password"
                                    name="password"
                                    required
                                    placeholder="Enter your password"
                                    class="w-full pl-12 pr-12 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                                <button type="button" onclick="togglePassword('loginPassword', 'loginEyeIcon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg id="loginEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="remember" class="ml-2 text-sm text-on-surface-variant">Remember me</label>
                        </div>
                        
                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            class="w-full py-4 bg-primary text-white rounded-xl font-semibold text-lg hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20"
                        >
                            Sign in to Lumina Academy!
                        </button>
                    </form>
                    </div>
                
                    <!-- Create Account Form -->
                    <div id="createAccountForm" class="form-panel inactive">
                    <!-- Welcome Text -->
                    <h2 class="text-3xl font-young-serif text-on-surface mb-2">Create Account</h2>
                    <p class="text-on-surface-variant mb-8">Join our academic community today.</p>
                    
                    <!-- Social Login Buttons -->
                    <div class="flex gap-4 mb-6">
                        <a href="https://accounts.google.com/" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-3 py-3 px-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="font-medium text-on-surface">Google</span>
                        </a>
                        <a href="https://www.linkedin.com/company/lumina-academy" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-3 py-3 px-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#0A66C2">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            <span class="font-medium text-on-surface">LinkedIn</span>
                        </a>
                    </div>
                    
                    <!-- Divider -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-primary font-medium text-sm">OR EMAIL</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>
                    
                    <!-- Form -->
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        
                        <!-- Global Error Messages for Registration -->
                        @if ($errors->any() && request()->old('_token') && request()->old('requested_role'))
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">FULL NAME</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="John Doe"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">EMAIL ADDRESS</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input 
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="name@lumina.edu"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">DATE OF BIRTH</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input
                                    type="date"
                                    name="date_of_birth"
                                    value="{{ old('date_of_birth') }}"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Parent Email (under 18 students) -->
                        <div id="publicParentEmailField">
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">PARENT EMAIL (UNDER 18 STUDENTS)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input
                                    id="publicParentEmail"
                                    type="email"
                                    name="parent_email"
                                    value="{{ old('parent_email') }}"
                                    placeholder="parent@email.com"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                            </div>
                            <p class="mt-2 text-xs text-on-surface-variant">Required when registering a student under 18.</p>
                        </div>

                        <div id="publicProgramField">
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">PROGRAM SELECTION</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M6 12h12M9 17h6"/>
                                    </svg>
                                </span>
                                <select
                                    id="publicProgramSelect"
                                    name="program_id"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface"
                                >
                                    <option value="">Select a program</option>
                                    @foreach ($availablePrograms as $program)
                                        <option value="{{ $program->id }}" @selected((string) old('program_id') === (string) $program->id)>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-2 text-xs text-on-surface-variant">
                                Start by choosing the language program, then select one course from that program.
                            </p>
                        </div>

                        <div id="publicCourseField">
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">COURSE SELECTION</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.343 2 8v10c0-1.657 2.462-3 5.5-3 1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.343 5.5 3v10c0-1.657-2.462-3-5.5-3-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </span>
                                <select
                                    id="publicCourseSelect"
                                    name="course_id"
                                    data-selected-course="{{ old('course_id') }}"
                                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface"
                                >
                                    <option value="">Select a program first</option>
                                </select>
                            </div>
                            <p class="mt-2 text-xs text-on-surface-variant">
                                Students must choose one admin-created course from the selected program before the registration can be approved.
                            </p>
                        </div>

                        <div id="publicRegistrationDocumentField">
                            <label id="publicRegistrationDocumentLabel" class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">REGISTRATION DOCUMENT</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.656-5.656L5.757 10.757a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </span>
                                <input
                                    id="publicRegistrationDocument"
                                    type="file"
                                    name="registration_document"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm text-on-surface file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-medium file:text-primary hover:file:bg-primary/15"
                                />
                            </div>
                            <p id="publicRegistrationDocumentHint" class="mt-2 text-xs text-on-surface-variant">
                                Upload the required file for the selected role.
                            </p>
                        </div>
                        
                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">PASSWORD</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input 
                                    id="registerPassword"
                                    type="password"
                                    name="password"
                                    required
                                    placeholder="Create a password"
                                    class="w-full pl-12 pr-12 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                                <button type="button" onclick="togglePassword('registerPassword', 'registerEyeIcon', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="Show password" title="Show password">
                                    <svg id="registerEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-2">CONFIRM PASSWORD</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input 
                                    id="confirmPassword"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    placeholder="Confirm your password"
                                    class="w-full pl-12 pr-12 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-on-surface placeholder:text-gray-400"
                                />
                                <button type="button" onclick="togglePassword('confirmPassword', 'confirmEyeIcon', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="Show password" title="Show password">
                                    <svg id="confirmEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Role Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-3">SELECT YOUR ROLE</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" id="registerRoleGroup">
                                <label class="role-option{{ old('requested_role', 'student') === 'student' ? ' selected' : '' }}">
                                    <input type="radio" name="requested_role" value="student"{{ old('requested_role', 'student') === 'student' ? ' checked' : '' }} />
                                    <span class="text-sm font-medium text-on-surface-variant role-text">Student</span>
                                </label>
                                <label class="role-option{{ old('requested_role') === 'teacher' ? ' selected' : '' }}">
                                    <input type="radio" name="requested_role" value="teacher"{{ old('requested_role') === 'teacher' ? ' checked' : '' }} />
                                    <span class="text-sm font-medium text-on-surface-variant role-text">Teacher</span>
                                </label>
                                <label class="role-option{{ old('requested_role') === 'parent' ? ' selected' : '' }}">
                                    <input type="radio" name="requested_role" value="parent"{{ old('requested_role') === 'parent' ? ' checked' : '' }} />
                                    <span class="text-sm font-medium text-on-surface-variant role-text">Parent</span>
                                </label>
                                <label class="role-option{{ old('requested_role') === 'secretary' ? ' selected' : '' }}">
                                    <input type="radio" name="requested_role" value="secretary"{{ old('requested_role') === 'secretary' ? ' checked' : '' }} />
                                    <span class="text-sm font-medium text-on-surface-variant role-text">Secretary</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            class="w-full py-4 bg-primary text-white rounded-xl font-semibold text-lg hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20"
                        >
                            Create Account
                        </button>
                    </form>
                    </div>
                </div> <!-- End forms-container -->
            </div>
        </div>
    </main>

    <script id="publicRegistrationCoursesData" type="application/json">
        @json(($availableCourses ?? collect())->values()->toArray())
    </script>

    <!-- JavaScript for Tab Switching and Password Toggle -->
    <script>
        function showSignIn() {
            // Update tabs
            document.getElementById('signInTab').classList.add('bg-primary', 'text-white', 'shadow-sm');
            document.getElementById('signInTab').classList.remove('text-on-surface-variant');
            document.getElementById('createAccountTab').classList.remove('bg-primary', 'text-white', 'shadow-sm');
            document.getElementById('createAccountTab').classList.add('text-on-surface-variant');
            
            // Smooth transition for forms
            const signInForm = document.getElementById('signInForm');
            const createAccountForm = document.getElementById('createAccountForm');
            
            signInForm.classList.remove('inactive');
            signInForm.classList.add('active');
            createAccountForm.classList.remove('active');
            createAccountForm.classList.add('inactive');
        }
        
        function showCreateAccount() {
            // Update tabs
            document.getElementById('createAccountTab').classList.add('bg-primary', 'text-white', 'shadow-sm');
            document.getElementById('createAccountTab').classList.remove('text-on-surface-variant');
            document.getElementById('signInTab').classList.remove('bg-primary', 'text-white', 'shadow-sm');
            document.getElementById('signInTab').classList.add('text-on-surface-variant');
            
            // Smooth transition for forms
            const signInForm = document.getElementById('signInForm');
            const createAccountForm = document.getElementById('createAccountForm');
            
            createAccountForm.classList.remove('inactive');
            createAccountForm.classList.add('active');
            signInForm.classList.remove('active');
            signInForm.classList.add('inactive');
        }
        
        function togglePassword(inputId, iconId, button) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (!input || !icon) {
                return;
            }
            
            if (input.type === 'password') {
                input.type = 'text';
                if (button) {
                    button.setAttribute('aria-label', 'Hide password');
                    button.setAttribute('title', 'Hide password');
                }
                // Change to eye-off icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                input.type = 'password';
                if (button) {
                    button.setAttribute('aria-label', 'Show password');
                    button.setAttribute('title', 'Show password');
                }
                // Change back to eye icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }
        
        // Check URL parameter on page load to show correct tab
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            const shouldOpenRegisterFromServer = document.body.dataset.openRegister === '1';
            
            if (shouldOpenRegisterFromServer) {
                showCreateAccount();
            }
            
            if (tab === 'register') {
                showCreateAccount();
            }
            
            // Initialize role selection handlers for registration form
            initRoleSelection('registerRoleGroup');

            const programSelect = document.getElementById('publicProgramSelect');
            if (programSelect) {
                programSelect.addEventListener('change', function () {
                    setCourseOptions(document.getElementById('publicCourseSelect'), this.value, '');
                });
            }
        });
        
        // Role selection handler
        function initRoleSelection(groupId) {
            const group = document.getElementById(groupId);
            if (!group) return;
            
            const options = group.querySelectorAll('.role-option');
            
            options.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                
                // Handle click on the label
                option.addEventListener('click', function() {
                    // Remove selected class from all options in this group
                    options.forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    this.classList.add('selected');
                });
                
                // Also handle change event for accessibility
                radio.addEventListener('change', function() {
                    options.forEach(opt => opt.classList.remove('selected'));
                    this.closest('.role-option').classList.add('selected');
                    toggleStudentOnlyFields(this.value);
                });
            });

            const selectedRole = group.querySelector('input[type="radio"]:checked')?.value ?? 'student';
            toggleStudentOnlyFields(selectedRole);
        }

        function toggleStudentOnlyFields(role) {
            const parentEmailField = document.getElementById('publicParentEmailField');
            const parentEmailInput = document.getElementById('publicParentEmail');
            const programField = document.getElementById('publicProgramField');
            const programSelect = document.getElementById('publicProgramSelect');
            const courseField = document.getElementById('publicCourseField');
            const courseSelect = document.getElementById('publicCourseSelect');
            const documentField = document.getElementById('publicRegistrationDocumentField');
            const documentInput = document.getElementById('publicRegistrationDocument');
            const documentLabel = document.getElementById('publicRegistrationDocumentLabel');
            const documentHint = document.getElementById('publicRegistrationDocumentHint');

            if (!parentEmailField || !parentEmailInput || !programField || !programSelect || !courseField || !courseSelect || !documentField || !documentInput || !documentLabel || !documentHint) {
                return;
            }

            const isStudent = role === 'student';
            const selectedCourseId = courseSelect.dataset.selectedCourse ?? '';
            const documentConfig = {
                student: {
                    label: 'UPLOAD BIRTH CERTIFICATE',
                    hint: 'Upload the student birth certificate as PDF, JPG, or PNG.',
                    accept: '.pdf,.jpg,.jpeg,.png',
                },
                teacher: {
                    label: 'UPLOAD C.V',
                    hint: 'Upload the teacher C.V as PDF or Word document.',
                    accept: '.pdf,.doc,.docx',
                },
                secretary: {
                    label: 'UPLOAD C.V',
                    hint: 'Upload the secretary C.V as PDF or Word document.',
                    accept: '.pdf,.doc,.docx',
                },
            };
            const activeDocumentConfig = documentConfig[role] ?? null;

            parentEmailField.style.display = isStudent ? 'block' : 'none';
            parentEmailInput.disabled = !isStudent;
            programField.style.display = isStudent ? 'block' : 'none';
            courseField.style.display = isStudent ? 'block' : 'none';
            programSelect.disabled = !isStudent;
            documentField.style.display = activeDocumentConfig ? 'block' : 'none';
            documentInput.disabled = !activeDocumentConfig;

            if (!isStudent) {
                parentEmailInput.value = '';
                programSelect.value = '';
                setCourseOptions(courseSelect, '', selectedCourseId);
                courseSelect.disabled = true;
            }

            if (activeDocumentConfig) {
                documentInput.value = '';
                documentLabel.textContent = activeDocumentConfig.label;
                documentHint.textContent = activeDocumentConfig.hint;
                documentInput.setAttribute('accept', activeDocumentConfig.accept);
            } else {
                documentInput.value = '';
                documentInput.removeAttribute('accept');
            }

            if (isStudent) {
                setCourseOptions(courseSelect, programSelect.value, selectedCourseId);
            }
        }

        function setCourseOptions(select, programId, selectedCourseId) {
            if (!select) {
                return;
            }

            const courses = JSON.parse(document.getElementById('publicRegistrationCoursesData')?.textContent ?? '[]');
            const filteredCourses = courses.filter((course) => String(course.program_id) === String(programId));
            const placeholder = !programId
                ? 'Select a program first'
                : filteredCourses.length === 0
                    ? 'No available courses for this program'
                    : 'Select a course';

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            filteredCourses.forEach((course) => {
                const option = new Option(`${course.name} (${Number(course.price).toLocaleString()} DA)`, String(course.id));
                select.appendChild(option);
            });

            const courseExists = filteredCourses.some((course) => String(course.id) === String(selectedCourseId));
            select.value = courseExists ? String(selectedCourseId) : '';
            select.disabled = filteredCourses.length === 0;
            select.dataset.selectedCourse = select.value;
        }
    </script>

    <!-- Footer -->
    <footer class="bg-on-surface py-6 px-8 mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <!-- Logo -->
            <div class="text-xl font-young-serif tracking-tight">
                <span class="text-primary">Lumina</span> <span class="text-gray-400">Academy</span>
            </div>
            
            <!-- Links -->
            <div class="flex items-center gap-8 text-sm text-gray-400 font-medium">
                <a href="mailto:admin@speakly.com" class="hover:text-primary transition-colors">SUPPORT</a>
                <a href="tel:+213345464654" class="hover:text-primary transition-colors">+213 345 464 654</a>
                <a href="https://maps.google.com/?q=Algiers,+Algeria" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors">ALGIERS, ALGERIA</a>
                <a href="https://www.instagram.com/luminaacademy" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors">INSTAGRAM</a>
            </div>
            
            <!-- Copyright -->
            <div class="text-sm text-gray-400">
                © 2026 LUMINA ACADEMY
            </div>
        </div>
    </footer>

</body>
</html>
