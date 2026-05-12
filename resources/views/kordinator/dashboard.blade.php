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
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
        <div class="space-y-1">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Verifikasi Nilai <span class="{{ $modulAktif == 'pemdi' ? 'text-emerald-600' : 'text-indigo-600' }}">Target {{ strtoupper($modulAktif) }}</span>
            </h2>
            <p class="text-slate-500 dark:text-gray-400 font-medium">
                Pilih periode tahun untuk validasi indikator kinerja.
            </p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
            <a href="{{ route('kordinator.dashboard', ['modul' => 'spbe']) }}" 
               class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all {{ $modulAktif == 'spbe' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Modul SPBE
            </a>
            <a href="{{ route('kordinator.dashboard', ['modul' => 'pemdi']) }}" 
               class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all {{ $modulAktif == 'pemdi' ? 'bg-white dark:bg-emerald-600 text-emerald-600 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Modul PEMDI
            </a>
        </div>
    </div>

    <!-- Grid Card dengan Penyesuaian Warna Modul -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($tahunTarget ?? [] as $tahun)
            <div class="target-card group relative bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 p-8 shadow-sm hover:shadow-xl">
                
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-6">
                        <!-- Warna Icon Box berubah sesuai modul -->
                        <div class="icon-box w-14 h-14 flex items-center justify-center bg-gradient-to-br {{ $modulAktif == 'pemdi' ? 'from-emerald-500 to-teal-600' : 'from-indigo-500 to-purple-600' }} text-white rounded-2xl shadow-lg">
                            <i class="fa-solid fa-calendar-check text-xl"></i>
                        </div>

                        @if($tahun->is_completed)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-circle-check mr-1.5"></i> Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse mr-2"></span> Perlu Verifikasi
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2 mb-8">
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tighter">
                            Periode {{ $tahun->tahun }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-gray-400 leading-relaxed">
                            Verifikasi usulan target untuk modul <strong>{{ strtoupper($modulAktif) }}</strong> tahun {{ $tahun->tahun }}.
                        </p>
                    </div>

                    <!-- Kirim parameter modul ke route tujuan -->
                    <a href="{{ route('koordinator.target.list', ['tahun' => $tahun->tahun, 'modul' => $modulAktif]) }}"
                       class="mt-auto flex items-center justify-center gap-3 w-full px-6 py-4 {{ $modulAktif == 'pemdi' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-2xl font-bold text-sm transition-all shadow-lg">
                        <span>Buka Verifikasi</span>
                        <i class="fa-solid fa-arrow-right-long group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        @empty
            <!-- State Kosong -->
        @endforelse
    </div>
</div>
@endsection