<!DOCTYPE html>
<html lang="id" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard P1')</title>
    @include('layouts.fav')
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-white dark:bg-gray-900 transition-colors duration-200">

    <nav x-data="{ open: false }"
         class="bg-blue-600 dark:bg-blue-600 text-white border-b border-blue-700 shadow-md sticky top-0 z-30">

       <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">

        <div class="flex items-center gap-3">
            <a href="{{ route('p1.nilai') }}" class="flex items-center gap-3">
                <x-application-logo class="h-9 w-auto fill-current text-white" />
                
                <div class="flex flex-col leading-tight">
                    <span class="font-bold text-xs md:text-sm uppercase tracking-tight max-w-[180px]">
                        Kementerian Pemberdayaan Perempuan 
                    </span>
                    <span class="font-bold text-xs md:text-sm uppercase tracking-tight max-w-[180px]">
                        dan Perlindungan Anak
                    </span>
                </div>
            </a>
        </div>

                <div class="hidden sm:flex items-center gap-6">

                    <a href="{{ route('p1.nilai') }}"
                       class="flex items-center gap-2 px-4 py-2 font-medium transition duration-300
                              border-b-2
                              {{ request()->routeIs('p1.nilai') ? 'border-[#D8E90B]' : 'border-transparent' }}
                              hover:border-[#D8E90B] hover:bg-blue-700 rounded-t-lg">Lihat Nilai</a>

                    <a href="{{ route('p1.chart') }}"
                       class="flex items-center gap-2 px-4 py-2 font-medium transition duration-300
                              border-b-2
                              {{ request()->routeIs('p1.chart') ? 'border-[#D8E90B]' : 'border-transparent' }}
                              hover:border-[#D8E90B] hover:bg-blue-700 rounded-t-lg">Dashboard</a>

                </div>

                <div class="hidden sm:flex items-center gap-4">

                    <button
                        @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                        class="p-2 rounded-full bg-blue-700 dark:bg-gray-700
                               text-white dark:text-[#D8E90B]
                               hover:bg-blue-800 dark:hover:bg-gray-600 transition shadow-inner border border-blue-500">
                        
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>

                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-700 hover:bg-blue-800 transition shadow border border-blue-500">
                                <div class="w-7 h-7 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm">{{ Auth::user()->name }}</span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-gray-400">Role: Pimpinan</div>
                            <x-dropdown-link :href="route('profile.edit')">
                                <i class="fa-solid fa-user me-2 text-gray-400"></i> Profile
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600">
                                    <i class="fa-solid fa-right-from-bracket me-2 text-red-600"></i> Keluar Sistem
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                </div>

                <div class="sm:hidden">
                    <button @click="open = !open" class="p-2 rounded-md hover:bg-blue-700 transition text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <div x-show="open" x-transition class="sm:hidden bg-blue-600 border-t border-blue-500">
            <a href="{{ route('dashboard') }}" class="block px-4 py-3 font-medium border-b-2 {{ request()->routeIs('dashboard') ? 'border-[#D8E90B]' : 'border-transparent' }}">Dashboard</a>
            <a href="{{ route('p1.nilai') }}" class="block px-4 py-3 font-medium border-b-2 {{ request()->routeIs('p1.nilai') ? 'border-[#D8E90B]' : 'border-transparent' }}">Lihat Nilai</a>
            <a href="{{ route('p1.chart') }}" class="block px-4 py-3 font-medium border-b-2 {{ request()->routeIs('p1.chart') ? 'border-[#D8E90B]' : 'border-transparent' }}">Visualisasi Chart</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6">
        <div class="mb-4">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white border-l-4 border-blue-600 pl-3">
                @yield('title')
            </h1>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            @yield('content')
        </div>
    </main>

</body>
</html>