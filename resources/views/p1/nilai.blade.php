@extends('layouts.p1')

@section('content')
@php
    $tahun = $tahunDipilih ?? date('Y');
    $tahunLalu = is_numeric($tahun) ? $tahun - 1 : '-';
    $modulAktif = request('modul', 'spbe');
    $indeksSekarang = \DB::table('hasil_indeks')
                        ->where('tahun', $tahun)
                        ->where('modul', $modulAktif)
                        ->first();
                        
    $indeksLalu = \DB::table('hasil_indeks')
                        ->where('tahun', $tahunLalu)
                        ->where('modul', $modulAktif)
                        ->first();

    $domainData = \DB::table('domain_hasil')
                        ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
                        ->where('domain_hasil.tahun', $tahun)
                        ->where('domain.modul', $modulAktif)
                        ->select('domain_hasil.*')
                        ->get()
                        ->keyBy('id_domain');

    $domainDataLalu = \DB::table('domain_hasil')
                        ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
                        ->where('domain_hasil.tahun', $tahunLalu)
                        ->where('domain.modul', $modulAktif)
                        ->select('domain_hasil.*', 'domain.nama_domain')
                        ->get()
                        ->keyBy('nama_domain');
    
    $aspekData = \DB::table('aspek_hasil')
                        ->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
                        ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('aspek_hasil.tahun', $tahun)
                        ->where('domain.modul', $modulAktif)
                        ->select('aspek_hasil.*')
                        ->get()
                        ->keyBy('id_aspek');

    $allDomainsList = \DB::table('domain')
                        ->where('tahun', $tahun)
                        ->where('modul', $modulAktif)
                        ->orderBy('urutan', 'asc')
                        ->get();
                        
    $allAspeks = \DB::table('aspek')
                        ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('aspek.tahun', $tahun)
                        ->where('domain.modul', $modulAktif)
                        ->select('aspek.*')
                        ->orderBy('aspek.urutan', 'asc')
                        ->get()
                        ->groupBy('id_domain');

    $spbeSekarang = $indeksSekarang->indeks_verif ?? ($indeksSekarang->indeks_spbe ?? 0);
    $spbeLama = $indeksLalu->indeks_verif ?? ($indeksLalu->indeks_spbe ?? 0);
    $selisih = $spbeSekarang - $spbeLama;

    $totalIndikator = \DB::table('indikator')
                        ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                        ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('indikator.tahun', $tahun)
                        ->where('domain.modul', $modulAktif)
                        ->count();

    $sudahDiverif = \DB::table('penilaian_kriteria')
                        ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                        ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                        ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('penilaian_kriteria.tahun', $tahun)
                        ->where('domain.modul', $modulAktif)
                        ->where('nilai_verifikator_internal', '>', 0)
                        ->count();
                        
    $persenPengerjaan = $totalIndikator > 0 ? ($sudahDiverif / $totalIndikator) * 100 : 0;
    
    $aspekCounter = 1;
    $indikatorCounter = 1;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 animate-in" x-data="{ modul: '{{ $modulAktif }}' }">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-black uppercase tracking-tighter text-gray-800 dark:text-white">
                Rincian Hasil Penilaian <span class="{{ $modulAktif == 'spbe' ? 'text-blue-600' : 'text-indigo-600' }}">{{ strtoupper($modulAktif) }}</span>
            </h2>
            <p class="text-sm text-gray-500 italic">Membandingkan Tahun {{ $tahunLalu }} dan {{ $tahun }}</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Selector Modul -->
            <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl border">
                <a href="{{ route('p1.hasil', ['tahun' => $tahun, 'modul' => 'spbe']) }}" 
                   class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $modulAktif == 'spbe' ? 'bg-white shadow text-blue-600' : 'text-gray-400' }}">
                    SPBE
                </a>
                <a href="{{ route('p1.hasil', ['tahun' => $tahun, 'modul' => 'pemdi']) }}" 
                   class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all {{ $modulAktif == 'pemdi' ? 'bg-white shadow text-indigo-600' : 'text-gray-400' }}">
                    PEMDI
                </a>
            </div>

            <!-- Selector Tahun -->
            <form method="get" action="{{ route('p1.hasil') }}" class="bg-white p-2 rounded-xl shadow border">
                <input type="hidden" name="modul" value="{{ $modulAktif }}">
                <select name="tahun" class="text-sm font-bold border-none focus:ring-0 bg-transparent text-gray-700" onchange="this.form.submit()">
                    @foreach($tahunList as $year)
                        <option value="{{ $year }}" {{ ($tahun == $year) ? 'selected' : '' }}>Tahun {{ $year }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        <div class="lg:col-span-2 rounded-3xl shadow-2xl p-8 bg-gradient-to-br {{ $modulAktif == 'spbe' ? 'from-blue-600 to-blue-800' : 'from-indigo-600 to-purple-800' }} text-white relative overflow-hidden group">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex-1">
                    <h5 class="text-white/70 text-sm font-bold uppercase tracking-widest mb-2">Indeks {{ strtoupper($modulAktif) }} Keseluruhan</h5>
                    <div class="flex items-baseline gap-4">
                        <span class="text-7xl font-black">{{ number_format($spbeSekarang, 2) }}</span>
                        @if($spbeLama > 0)
                            <div class="flex items-center text-sm font-bold {{ $selisih >= 0 ? 'bg-emerald-400 text-emerald-900' : 'bg-rose-400 text-rose-900' }} px-2 py-1 rounded-lg">
                                {!! $selisih >= 0 ? '↑' : '↓' !!} {{ abs(number_format($selisih, 2)) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 inline-block px-4 py-1 rounded-full bg-white/20 text-[10px] font-bold uppercase">Predikat: {{ $indeksSekarang->predikat ?? '-' }}</div>
                </div>
                <div class="relative w-32 h-32 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/10" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="10" fill="transparent" 
                            stroke-dasharray="364.4" 
                            style="stroke-dashoffset: {{ 364.4 - (364.4 * ($persenPengerjaan / 100)) }}; transition: stroke-dashoffset 2s ease-out;"
                            class="text-emerald-400" />
                    </svg>
                    <span class="absolute text-xl font-bold">{{ round($persenPengerjaan) }}%</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl shadow-xl p-8 bg-white dark:bg-gray-800 border flex flex-col justify-center text-center">
            <h5 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-widest">Target {{ strtoupper($modulAktif) }} {{ $tahun }}</h5>
            <p class="text-5xl font-black {{ $modulAktif == 'spbe' ? 'text-blue-500' : 'text-indigo-500' }} mb-2">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</p>
            <div class="h-1.5 w-full bg-gray-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full {{ $modulAktif == 'spbe' ? 'bg-blue-500' : 'bg-indigo-500' }}" style="width: {{ (($indeksSekarang->target_spbe ?? 0)/5)*100 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto shadow-2xl rounded-2xl border border-gray-200">
        <table class="min-w-full table-custom bg-white dark:bg-gray-800 text-xs text-center border-collapse">
            <thead>
                <tr class="bg-gray-900 text-white font-bold uppercase">
                    <th rowspan="2" class="w-1/3 text-left pl-6 py-4">Domain / Aspek / Indikator</th>
                    <th class="border-l border-gray-700">Tahun {{ $tahunLalu }}</th>
                    <th colspan="3" class="bg-gray-800 border-l border-gray-700 italic text-yellow-400">Tahun {{ $tahun }}</th>
                </tr>
                <tr class="bg-gray-800 text-white font-bold text-[10px]">
                    <th class="border-l border-gray-700">Nilai Verif</th>
                    <th class="text-amber-400 border-l border-gray-700">Target</th>
                    <th class="text-blue-400 border-l border-gray-700">Mandiri</th>
                    <th class="text-emerald-400 border-l border-gray-700">Verifikator</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row Total Indeks -->
                <tr class="{{ $modulAktif == 'spbe' ? 'bg-blue-50' : 'bg-indigo-50' }} font-black border-b-2 border-gray-300">
                    <td class="text-left pl-6 py-4 uppercase tracking-widest text-gray-800">INDEKS {{ strtoupper($modulAktif) }}</td>
                    <td class="text-gray-600">{{ number_format($spbeLama, 2) }}</td>
                    <td class="text-amber-700">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</td>
                    <td class="text-blue-700">{{ number_format($indeksSekarang->indeks_spbe ?? 0, 2) }}</td>
                    <td class="text-emerald-700 text-lg">{{ number_format($indeksSekarang->indeks_verif ?? 0, 2) }}</td>
                </tr>

                @foreach($allDomainsList as $dom)
                    @php
                        $curDom = $domainData[$dom->id_domain] ?? null;
                        $oldDom = $domainDataLalu[$dom->nama_domain] ?? null;
                        $valDomLalu = ($oldDom->domain_verif ?? 0) > 0 ? $oldDom->domain_verif : ($oldDom->nilai_domain ?? 0);
                    @endphp
                    <tr class="bg-gray-50 font-bold border-t border-gray-200">
                        <td class="text-left pl-8 py-3 uppercase text-gray-700">Domain {{ $loop->iteration }}: {{ $dom->nama_domain }}</td>
                        <td>{{ number_format($valDomLalu, 2) }}</td>
                        <td class="text-amber-700">{{ number_format($curDom->target ?? 0, 2) }}</td>
                        <td class="text-blue-700">{{ number_format($curDom->nilai_domain ?? 0, 2) }}</td>
                        <td class="text-emerald-700 font-black">{{ number_format($curDom->domain_verif ?? 0, 2) }}</td>
                    </tr>

                    @if(isset($allAspeks[$dom->id_domain]))
                        @foreach($allAspeks[$dom->id_domain] as $aspek)
                            @php
                                $curAsp = $aspekData[$aspek->id_aspek] ?? null;
                                $oldAsp = null; // Karena aspeklalu di-keyBy nama_aspek
                                $valAspLalu = 0;
                                if(isset($aspekDataLalu[$aspek->nama_aspek])){
                                    $oldAsp = $aspekDataLalu[$aspek->nama_aspek];
                                    $valAspLalu = ($oldAsp->aspek_verif ?? 0) > 0 ? $oldAsp->aspek_verif : ($oldAsp->nilai_aspek ?? 0);
                                }
                            @endphp
                            <tr class="bg-white border-t border-gray-100">
                                <td class="text-left pl-12 text-blue-800 font-medium italic">Aspek {{ $aspekCounter++ }}: {{ $aspek->nama_aspek }}</td>
                                <td>{{ number_format($valAspLalu, 2) }}</td>
                                <td class="text-amber-700">{{ number_format($curAsp->target ?? 0, 2) }}</td>
                                <td class="text-blue-700">{{ number_format($curAsp->nilai_aspek ?? 0, 2) }}</td>
                                <td class="text-emerald-700 font-semibold">{{ number_format($curAsp->aspek_verif ?? 0, 2) }}</td>
                            </tr>

                            @php
                                $indikators = \DB::table('indikator')->where('id_aspek', $aspek->id_aspek)->where('tahun', $tahun)->orderBy('urutan', 'asc')->get();
                            @endphp
                            @foreach($indikators as $ind)
                                @php
                                    $det = \DB::table('penilaian_kriteria')->where('id_indikator', $ind->id_indikator)->where('tahun', $tahun)->first();
                                    $indLalu = \DB::table('indikator')->where('nama_indikator', $ind->nama_indikator)->where('tahun', $tahunLalu)->first();
                                    $valIndLalu = 0;
                                    if($indLalu){
                                        $detLalu = \DB::table('penilaian_kriteria')->where('id_indikator', $indLalu->id_indikator)->where('tahun', $tahunLalu)->first();
                                        $valIndLalu = ($detLalu->nilai_verifikator_internal ?? 0) > 0 ? $detLalu->nilai_verifikator_internal : ($detLalu->nilai_asesor_internal ?? 0);
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors bg-white/50 border-t border-gray-50">
                                    <td class="text-left pl-16 text-gray-500 py-2 leading-relaxed">
                                        Indikator {{ $indikatorCounter++ }}: {{ $ind->nama_indikator }}
                                    </td>
                                    <td class="text-gray-400">{{ number_format($valIndLalu, 2) }}</td>
                                    <td class="text-amber-600/70">{{ number_format($det->nilai_target ?? 0, 2) }}</td>
                                    <td class="text-blue-600/70">{{ number_format($det->nilai_asesor_internal ?? 0, 2) }}</td>
                                    <td class="font-black text-emerald-700 bg-emerald-50/30">{{ number_format($det->nilai_verifikator_internal ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection