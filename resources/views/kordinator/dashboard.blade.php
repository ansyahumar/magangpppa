@extends('layouts.kordinator')

@section('content')
<style>
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-list { animation: slideInUp 0.5s ease-out forwards; }
    
    .target-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .target-card:hover {
        transform: translateY(-5px);
        border-color: #4f46e5;
    }
    .icon-box {
        transition: all 0.3s ease;
    }
    .target-card:hover .icon-box {
        transform: rotate(-10deg) scale(1.1);
    }
</style>

<div class="px-4 py-6 max-w-7xl mx-auto space-y-8 animate-list">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
        <div class="space-y-1">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Verifikasi Nilai <span class="text-indigo-600">Target</span>
            </h2>
            <p class="text-slate-500 dark:text-gray-400 font-medium">
                Pilih periode tahun untuk melakukan validasi indikator kinerja SPBE.
            </p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
            <i class="fa-solid fa-circle-check text-indigo-600 dark:text-indigo-400"></i>
            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">Sistem Validasi Aktif</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($tahunTarget ?? [] as $tahun)
            <div class="target-card group relative bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10">
                <div class="absolute top-6 right-8 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
                    <i class="fa-solid fa-bullseye text-8xl text-slate-900 dark:text-white"></i>
                </div>

                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-6">
    <div class="icon-box w-14 h-14 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-2xl shadow-lg">
        <i class="fa-solid fa-calendar-check text-xl"></i>
    </div>

    @if($tahun->is_completed)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check mr-1.5"></i>
            Terverifikasi
        </span>
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse mr-2"></span>
            Perlu Verifikasi
        </span>
    @endif
</div>

                    <div class="space-y-2 mb-8">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tighter">
                            Periode {{ $tahun->tahun }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-gray-400 leading-relaxed">
                            Terdapat usulan target baru yang memerlukan tinjauan teknis sebelum ditetapkan sebagai acuan penilaian.
                        </p>
                    </div>

                    <a href="{{ route('koordinator.target.list', $tahun->tahun) }}"
                       class="mt-auto flex items-center justify-center gap-3 w-full px-6 py-4 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 text-white rounded-2xl font-bold text-sm transition-all active:scale-95 shadow-lg">
                        <span>Buka Verifikasi</span>
                        <i class="fa-solid fa-arrow-right-long group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-900/40 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
                <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center mb-6 shadow-sm">
                    <i class="fa-solid fa-box-open text-4xl text-slate-300"></i>
                </div>
                <h4 class="text-xl font-bold text-slate-800 dark:text-white">Data Tidak Ditemukan</h4>
                <p class="text-slate-500 dark:text-gray-400 mt-2 max-w-xs text-center">
                    Belum ada usulan target yang masuk ke dalam sistem untuk saat ini.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection