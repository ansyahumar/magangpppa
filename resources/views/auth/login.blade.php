<x-guest-layout>

    <div class="fixed inset-0 -z-10 h-full w-full bg-no-repeat bg-cover bg-center" 
         style="background-image: url('{{ asset('images/background.svg') }}'); pointer-events: none;">
    </div>

    
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
            <ul class="list-disc list-inside text-xs text-red-600 font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

      
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required />
                
               
                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="eye-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4">
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 !bg-blue-600 hover:!bg-blue-700 text-white font-semibold rounded-lg shadow">
                {{ __('Log in') }}
            </button>
            <button type="button" onclick="window.location.href='{{ url('/') }}'" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow">
                {{ __('Kembali') }}
            </button>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyePath = document.getElementById('eye-path');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.888 9.888L2 2m10 8l10 10');
            } else {
                passwordInput.type = 'password';
             
                eyePath.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
            }
        }
    </script>
</x-guest-layout>