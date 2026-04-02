<!DOCTYPE html>
<html lang="id" class="h-full" x-data="themeManager()" x-init="initTheme()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.csrfToken = "{{ csrf_token() }}";</script>
    <title>@yield('title', 'Admin Panel')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#D8E90B' } } }
        }
    </script>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
        
        /* CKEditor Custom Styles */
        .ck-editor__editable { min-height: 200px !important; max-height: 400px !important; }
        .ck.ck-editor { width: 100% !important; }
        .ck-editor__editable_inline { min-height: 200px; color: black !important; }
        .ck-body-wrapper { z-index: 9999; }
        
        .content-preview table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        .content-preview table td, .content-preview table th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>

<body class="h-full bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

<div class="min-h-screen flex">

    <div x-show="sidebarOpen" 
         x-transition.opacity
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/50 z-20 lg:hidden" 
         x-cloak></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 w-64 bg-blue-600 dark:bg-blue-800 border-r border-blue-700 dark:border-blue-900 shadow-xl z-30 transform transition-transform duration-300 lg:translate-x-0 flex flex-col">
        
        <div class="p-5 border-b border-blue-700 dark:border-blue-900 flex items-center gap-3 shrink-0">
            <x-application-logo class="h-10 w-auto" />
            <h1 class="text-xl font-bold text-white tracking-tight">Admin Panel</h1>
        </div>

      <nav class="flex-1 overflow-y-auto p-4 space-y-2 text-white custom-scrollbar" x-data="{ openSubMenu: null }">
    
    <div class="relative group">
        <button @click="handleToggle('indeks_group')" 
            class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg font-medium transition-all border-l-4"
            :class="(openMenu === 'indeks_group') ? 'border-primary bg-blue-700 shadow-inner' : 'border-transparent hover:bg-blue-700/50'">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-base font-semibold">Indeks Penilaian</span>
            </div>
            <svg class="w-4 h-4 transition-transform duration-300" :class="openMenu === 'indeks_group' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="openMenu === 'indeks_group'" x-cloak class="ml-4 mt-1 space-y-1 border-l border-white/20">
            <div class="py-1">
                <button @click="openSubMenu = (openSubMenu === 'spbe' ? null : 'spbe')" 
                    class="w-full flex items-center justify-between px-4 py-2 text-sm hover:bg-white/5 rounded-r-lg transition-all">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span>SPBE</span>
                    </div>
                    <svg class="w-3 h-3 transition-transform" :class="openSubMenu === 'spbe' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="openSubMenu === 'spbe'" x-cloak class="ml-6 mt-1 space-y-1 border-l border-white/10">
                    @php
                        $spbeItems = [
                            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['label' => 'Hasil Penilaian', 'route' => 'admin.hasil'],
                            ['label' => 'Monitoring', 'route' => 'admin.monitoring'],
                        ];
                    @endphp

                    @foreach($spbeItems as $item)
                    <a href="{{ route($item['route']) }}" 
                       class="block px-4 py-2 text-xs transition-all rounded-r-lg"
                       :class="'{{ request()->url() }}' === '{{ route($item['route']) }}' ? 'text-primary font-bold bg-white/10' : 'text-white/70 hover:text-primary hover:bg-white/5'">
                        • {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('master.index') }}"
        class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium transition-all border-l-4
        {{ request()->routeIs('master.*') ? 'border-primary bg-blue-700' : 'border-transparent hover:bg-blue-700/50' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z" />
        </svg>
        <span>Master Data</span>
    </a>

    <a href="{{ route('admin.users.index') }}"
        class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium transition-all border-l-4
        {{ request()->routeIs('admin.users.*') ? 'border-primary bg-blue-700' : 'border-transparent hover:bg-blue-700/50' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>Manajemen Akun</span>
    </a>
</nav>
    </aside>

    <div class="flex-1 flex flex-col lg:ml-64">

        <header class="sticky top-0 z-20 bg-blue-600 dark:bg-blue-900 text-white px-4 py-4 flex items-center justify-between shadow-md">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 hover:bg-white/10 rounded-lg">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="ml-auto flex items-center gap-3">
                <button @click="toggleTheme()" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition">
                    <template x-if="darkMode">
                        <svg class="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.243 3.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM17 10a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zM14.243 16.95a1 1 0 011.414-1.414l.707.707a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 010-1.414zM10 17a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.757 16.95a1 1 0 010-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414 0zM3 10a1 1 0 011-1h1a1 1 0 110 2H4a1 1 0 01-1-1zM5.757 5.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zM10 8a2 2 0 100 4 2 2 0 000-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="!darkMode">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </template>
                </button>

                <a href="{{ route('admin.profileadmin') }}" class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-full border border-white/20 hover:bg-white/20 transition">
                    <div class="w-6 h-6 bg-blue-500 flex items-center justify-center rounded-full text-[10px] font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold">{{ Auth::user()->name }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-1.5 rounded-lg text-sm font-bold transition shadow-sm">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
function themeManager() {
    return {
        sidebarOpen: false,
        darkMode: false,
        openMenu: null,
        lockedMenu: null,

        initTheme() {
            this.darkMode = localStorage.getItem('theme') === 'dark' || 
                           (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            this.applyTheme();

            @if(request()->routeIs('admin.dashboard') || request()->routeIs('admin.hasil') || request()->routeIs('admin.monitoring') || request()->routeIs('master.index'))
                this.lockedMenu = 'spbe_group';
                this.openMenu = 'spbe_group';
            @endif
        },

        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            this.applyTheme();
        },

        applyTheme() {
            if (this.darkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        },

        handleHover(menu) {
            if (!this.lockedMenu) {
                this.openMenu = menu;
            }
        },

        handleLeave() {
            this.openMenu = this.lockedMenu;
        },

        handleToggle(menu) {
            if (this.lockedMenu === menu) {
                this.lockedMenu = null;
                this.openMenu = null;
            } else {
                this.lockedMenu = menu;
                this.openMenu = menu;
            }
        }
    }
}
</script>

</body>
</html>