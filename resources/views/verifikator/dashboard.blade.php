@extends('layouts.verifikator')

@section('title', 'Penilaian Masuk (Menunggu Verifikasi)')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Evaluasi</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Silahkan pilih tahun evaluasi untuk proses verifikasi atau hitung indeks.</p>
        </div>
        <div class="hidden md:block">
            <i class="fa-solid fa-clipboard-list text-blue-600/20 dark:text-blue-400/20 text-4xl"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($tahunSelesaiAsesor as $tahun)
            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300">
                
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    

                    <h3 class="text-lg font-extrabold text-gray-800 dark:text-white tracking-tight">
                        Tahun Evaluasi {{ $tahun }}
                    </h3>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-6 flex-grow">
                        Data telah difinalisasi oleh asesor. Anda dapat mulai memverifikasi atau menghitung indeks akhir.
                    </p>

                    <div class="space-y-3 mt-auto">
                        <a href="{{ route('verifikator.list', $tahun) }}" 
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
                <h4 class="text-gray-800 dark:text-white font-bold">Tidak Ada Penilaian</h4>
            </div>
        @endforelse
    </div>
</div>
@endsection