@extends('layouts.verifikator')

@section('title', 'Penilaian Masuk (Menunggu Verifikasi)')

@section('content')
<!-- Tambahkan x-data untuk kontrol Tab menggunakan Alpine.js -->
<div class="space-y-6" x-data="{ activeTab: 'spbe' }">
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700 gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Evaluasi</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Silahkan pilih modul dan tahun evaluasi untuk proses verifikasi.</p>
        </div>
        
        <!-- Tab Switcher -->
        <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl w-fit border border-gray-200 dark:border-gray-700">
            <button @click="activeTab = 'spbe'" 
                :class="activeTab === 'spbe' ? 'bg-white dark:bg-gray-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500'"
                class="px-6 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                SPBE
            </button>
            <button @click="activeTab = 'pemdi'" 
                :class="activeTab === 'pemdi' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500'"
                class="px-6 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                PEMDI
            </button>
        </div>
    </div>

    <!-- Container Grid SPBE -->
    <div x-show="activeTab === 'spbe'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition>
        @forelse($tahunSPBE as $tahun)
            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-md uppercase">SPBE</span>
                    </div>

                    <h3 class="text-lg font-extrabold text-gray-800 dark:text-white tracking-tight">
                        Tahun Evaluasi {{ $tahun }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-6 flex-grow">
                        Data modul SPBE telah difinalisasi. Anda dapat mulai memverifikasi atau menghitung indeks akhir.
                    </p>

                    <div class="space-y-3 mt-auto">
                        <a href="{{ route('verifikator.list', ['tahun' => $tahun, 'modul' => 'spbe']) }}" 
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-all active:scale-95 shadow-lg shadow-blue-500/20">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            Mulai Verifikasi
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-gray-50/50 dark:bg-gray-900/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-inbox text-3xl text-gray-300 mb-4"></i>
                <h4 class="text-gray-800 dark:text-white font-bold">Tidak Ada Penilaian SPBE</h4>
            </div>
        @endforelse
    </div>

    <!-- Container Grid PEMDI -->
    <div x-show="activeTab === 'pemdi'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition>
        @forelse($tahunPEMDI as $tahun)
            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-indigo-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-md uppercase">PEMDI</span>
                    </div>

                    <h3 class="text-lg font-extrabold text-gray-800 dark:text-white tracking-tight">
                        Tahun Evaluasi {{ $tahun }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-6 flex-grow">
                        Data modul PEMDI telah difinalisasi. Anda dapat mulai memverifikasi atau menghitung indeks akhir.
                    </p>

                    <div class="space-y-3 mt-auto">
                        <a href="{{ route('verifikator.list', ['tahun' => $tahun, 'modul' => 'pemdi']) }}" 
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all active:scale-95 shadow-lg shadow-indigo-500/20">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            Mulai Verifikasi
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-gray-50/50 dark:bg-gray-900/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <i class="fa-solid fa-inbox text-3xl text-gray-300 mb-4"></i>
                <h4 class="text-gray-800 dark:text-white font-bold">Tidak Ada Penilaian PEMDI</h4>
            </div>
        @endforelse
    </div>
</div>
@endsection