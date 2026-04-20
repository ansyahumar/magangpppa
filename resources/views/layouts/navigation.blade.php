<nav x-data="{ open: false }"
     class="bg-blue-600 dark:bg-blue-600 text-white border-b border-blue-700 shadow-md sticky top-0 z-30">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

           
           <div class="flex items-center gap-3">
    @php
        
        $role = strtolower(Auth::user()->role); 
        
        
        $url = match($role) {
            'admin'       => route('admin.dashboard'),
            'verifikator' => route('verifikator.verifikasi'),
            'p2'          => route('p2.dashboard'),
            'p1'          => route('p1.nilai'), 
            'kordinator'  => route('kordinator.dashboard'), 
            default       => route('dashboard'), 
        };
    @endphp

    <a href="{{ $url }}" class="flex items-center gap-2">
        <x-application-logo class="h-9 w-auto fill-current text-white" />
    </a>
</div>

        
            <div class="hidden sm:flex items-center gap-4">

                <button
                    @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                    class="p-2 rounded-full bg-gray-100 dark:bg-gray-700
                           text-gray-700 dark:text-[#D8E90B]
                           hover:bg-gray-200 dark:hover:bg-gray-600 transition shadow-sm">

                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3
                                 m15.364-6.364l-.707.707M6.343 17.657l-.707.707
                                 m12.728 0l-.707-.707M6.343 6.343l-.707-.707
                                 M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>

                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646
                                 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center gap-2 px-4 py-2 rounded-full
                                   bg-blue-700 hover:bg-blue-800 transition shadow">
                            <div
                                class="w-7 h-7 rounded-full bg-white text-blue-600
                                       flex items-center justify-center font-bold">
                                {{ strtoupper(Auth::user()->name[0]) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-gray-400">Role: Asesor</div>
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>

            </div>
        </div>
    </div>

  
</nav>