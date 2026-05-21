<x-guest-layout>

    <!-- Ambient Floating Graphics Background -->
    <div class="fixed inset-0 -z-10 h-full w-full bg-slate-50 dark:bg-slate-950 transition-colors duration-500 overflow-hidden" style="pointer-events: none;">
        <div class="ambient-blob-1"></div>
        <div class="ambient-blob-2"></div>
    </div>

    <!-- Inject Force-Animation Engine -->
    <style>
        /* 1. Animasi Latar Belakang Bergerak */
        @keyframes floatGlow1 {
            0% { transform: translate(0px, 0px) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.1); }
        }
        @keyframes floatGlow2 {
            0% { transform: translate(0px, 0px) scale(1); }
            100% { transform: translate(-30px, -20px) scale(1.05); }
        }
        .ambient-blob-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.12), rgba(99, 102, 241, 0.05));
            filter: blur(64px);
            top: -160px;
            right: -80px;
            animation: floatGlow1 12s ease-in-out infinite alternate;
        }
        .ambient-blob-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: linear-gradient(to bottom right, rgba(244, 63, 94, 0.1), rgba(245, 158, 11, 0.05));
            filter: blur(64px);
            bottom: -80px;
            left: -80px;
            animation: floatGlow2 15s ease-in-out infinite alternate-reverse;
        }

        /* 2. Animasi Muncul Lembut (Fade In) */
        @keyframes customFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .force-fade-in {
            animation: customFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
        }

        /* 3. Efek Transisi Fokus untuk Input & Tombol */
        .premium-input-frame input, 
        .premium-input-frame select, 
        .premium-input-frame textarea {
            background-color: rgba(255, 255, 255, 0.5) !important;
            backdrop-filter: blur(4px) !important;
            border-radius: 12px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .dark .premium-input-frame input {
            background-color: rgba(15, 23, 42, 0.5) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .premium-input-frame input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15) !important;
        }

        /* 4. Efek Fisik Tombol Masuk & Kembali */
        .btn-animate-primary {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        .btn-animate-primary:hover {
            transform: translateY(-3px) scale(1.01) !important;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3) !important;
        }
        .btn-animate-primary:active {
            transform: translateY(-1px) scale(1) !important;
        }

        .btn-animate-secondary {
            transition: all 0.3s ease !important;
        }
        .btn-animate-secondary:hover {
            transform: translateY(-2px) !important;
            background-color: rgba(248, 250, 252, 0.8) !important;
        }
        .dark .btn-animate-secondary:hover {
            background-color: rgba(30, 41, 59, 0.5) !important;
        }
        .btn-animate-secondary:active {
            transform: translateY(0) !important;
        }
    </style>

    <!-- Animated Error Alert Group -->
    @if ($errors->any())
        <div class="mb-5 p-4 bg-white/70 dark:bg-rose-950/20 backdrop-blur-md border border-rose-500/30 border-l-4 border-l-rose-500 rounded-xl force-fade-in shadow-sm">
            <div class="flex items-center gap-2 mb-1.5">
                <i class="bi bi-exclamation-triangle-fill text-rose-500 text-sm"></i>
                <span class="text-xs font-bold text-rose-700 dark:text-rose-400">Gagal Masuk Aplikasi</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-600/90 dark:text-rose-400/90 space-y-0.5 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Login Form Action Container -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        
        <!-- Email Input Group -->
        <div class="premium-input-frame">
            <x-input-label for="email" :value="__('Email')" class="text-slate-700 dark:text-slate-300 font-medium text-xs tracking-wide" />
            <div class="relative mt-1">
                <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>
            <div class="force-fade-in">
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-500" />
            </div>
        </div>

        <!-- Password Input Group -->
        <div class="premium-input-frame">
            <x-input-label for="password" :value="__('Password')" class="text-slate-700 dark:text-slate-300 font-medium text-xs tracking-wide" />
            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-11" type="password" name="password" required />
                
                <button type="button" onclick="togglePassword()" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors duration-300 focus:outline-none"
                    aria-label="Tampilkan Password">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 active:scale-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="eye-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path id="eye-body" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <div class="force-fade-in">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-500" />
            </div>
        </div>

        <!-- Action Grid Button Layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-4">
            <!-- Premium Primary Login Button -->
            <button type="submit" 
                class="btn-animate-primary w-full inline-flex justify-center items-center px-5 py-3 !bg-gradient-to-r !from-blue-600 !to-indigo-600 hover:!from-blue-700 hover:!to-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20">
                <i class="bi bi-box-arrow-in-right mr-2 text-base"></i> {{ __('Log in') }}
            </button>
            
            <!-- Secondary Back Button -->
            <button type="button" onclick="window.location.href='{{ url('/') }}'" 
                class="btn-animate-secondary w-full inline-flex justify-center items-center px-5 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-sm rounded-xl shadow-sm">
                <i class="bi bi-arrow-left-short mr-1 text-lg"></i> {{ __('Kembali') }}
            </button>
        </div>
    </form>

    <!-- JavaScript Interactive Module -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyePath = document.getElementById('eye-path');
            const eyeBody = document.getElementById('eye-body');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.888 9.888L2 2m10 8l10 10');
                if (eyeBody) eyeBody.style.display = 'none';
            } else {
                passwordInput.type = 'password';
                eyePath.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
                if (eyeBody) {
                    eyeBody.style.display = 'block';
                    eyeBody.setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
                }
            }
        }
    </script>
</x-guest-layout>