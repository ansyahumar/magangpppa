@extends('layouts.kordinator')

@section('content')
@php
    $modulAktif = request('modul', 'spbe');
    $tahun = $tahunDipilih ?? date('Y');
    $tahunLalu = is_numeric($tahun) ? $tahun - 1 : '-';
    $indeksSekarang = \DB::table('hasil_indeks')
                        ->where('tahun', $tahun)
                        ->where('modul', $modulAktif)
                        ->first();
                        
    $indeksLalu = \DB::table('hasil_indeks')
                    ->where('tahun', $tahunLalu)
                    ->where('modul', $modulAktif)
                    ->first();
    $spbeSekarang = $indeksSekarang->indeks_verif ?? ($indeksSekarang->indeks_spbe ?? 0);
    $spbeLama = $indeksLalu->indeks_verif ?? ($indeksLalu->indeks_spbe ?? 0);
    $selisih = $spbeSekarang - $spbeLama;
    $totalIndikator = \DB::table('indikator')->where('tahun', $tahun)->count();
    $sudahDiverif = \DB::table('penilaian_kriteria')
                        ->where('tahun', $tahun)
                        ->where('nilai_verifikator_internal', '>', 0)
                        ->count();
    
    $persenPengerjaan = $totalIndikator > 0 ? ($sudahDiverif / $totalIndikator) * 100 : 0;
    $allDomainsList = \DB::table('domain')
                        ->where('tahun', $tahun)
                        ->orderBy('urutan', 'asc')
                        ->get();
    
    $totalIndikator = \DB::table('indikator')
                        ->where('tahun', $tahun)
                        ->count();
@endphp

<style>
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: slideUp 0.6s ease-out forwards; opacity: 0; }
    .table-custom th, .table-custom td { border: 1px solid #d1d5db; padding: 10px 8px; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8 animate-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black uppercase tracking-tighter text-gray-800 dark:text-white">
                Rincian Hasil <span class="{{ $modulAktif == 'pemdi' ? 'text-emerald-600' : 'text-blue-600' }}">{{ strtoupper($modulAktif) }}</span>
            </h2>
            <p class="text-sm text-gray-500 italic">Perbandingan Data Tahunan ({{ $tahunLalu }} vs {{ $tahun }})</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border shadow-sm">
                <a href="{{ route('kordinator.nilai', ['modul' => 'spbe', 'tahun' => $tahun]) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $modulAktif == 'spbe' ? 'bg-white dark:bg-blue-600 text-blue-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    SPBE
                </a>
                <a href="{{ route('kordinator.nilai', ['modul' => 'pemdi', 'tahun' => $tahun]) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $modulAktif == 'pemdi' ? 'bg-white dark:bg-emerald-600 text-emerald-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    PEMDI
                </a>
            </div>

            <!-- Dropdown Tahun -->
            <form method="get" action="{{ route('kordinator.nilai') }}" class="bg-white p-2 rounded-lg shadow border flex items-center">
                <input type="hidden" name="modul" value="{{ $modulAktif }}">
                <select name="tahun" class="text-sm font-bold border-none focus:ring-0 bg-transparent text-gray-700" onchange="this.form.submit()">
                    @foreach($tahunList as $year)
                        <option value="{{ $year }}" {{ ($tahun == $year) ? 'selected' : '' }}>Tahun {{ $year }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        <!-- Kartu Utama -->
        <div class="lg:col-span-2 rounded-3xl shadow-2xl p-8 text-white relative overflow-hidden group 
            {{ $modulAktif == 'pemdi' ? 'bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800' : 'bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800' }}">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex-1">
                    <h5 class="text-white/80 text-sm font-bold uppercase tracking-widest mb-2">Indeks {{ strtoupper($modulAktif) }} Keseluruhan</h5>
                    <div class="flex items-baseline gap-4">
                        <span class="text-7xl font-black">{{ number_format($spbeSekarang, 2) }}</span>
                        <!-- ... (Sisanya tetap sama) ... -->
                    </div>
                </div>
                <!-- Progress Circle -->
                <div class="relative w-32 h-32 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/10" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="10" fill="transparent" 
                            stroke-dasharray="364.4" 
                            style="stroke-dashoffset: {{ 364.4 - (364.4 * ($persenPengerjaan / 100)) }};"
                            class="{{ $modulAktif == 'pemdi' ? 'text-yellow-300' : 'text-emerald-400' }}" />
                    </svg>
                    <span class="absolute text-xl font-bold">{{ round($persenPengerjaan) }}%</span>
                </div>
            </div>
        </div>

        <!-- Kartu Target -->
        <div class="rounded-3xl shadow-xl p-8 bg-white dark:bg-gray-800 border flex flex-col justify-center text-center">
            <h5 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-widest">Target {{ strtoupper($modulAktif) }} {{ $tahun }}</h5>
            <p class="text-5xl font-black {{ $modulAktif == 'pemdi' ? 'text-emerald-500' : 'text-indigo-500' }} mb-2">
                {{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}
            </p>
            <!-- ... -->
        </div>
    </div>
            <div class="h-1.5 w-full bg-gray-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-indigo-500" style="width: {{ (($indeksSekarang->target_spbe ?? 0)/5)*100 }}%"></div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto shadow-2xl rounded-lg">
        <table class="min-w-full table-custom bg-white dark:bg-gray-800 text-xs text-center">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-900 font-bold uppercase text-gray-700 dark:text-gray-200">
                    <th rowspan="2" class="w-1/3 text-left pl-4">Domain / Aspek / Indikator</th>
                    <th>Tahun {{ $tahunLalu }}</th>
                    <th colspan="3" class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200">Tahun {{ $tahun }}</th>
                </tr>
                <tr class="bg-gray-100 dark:bg-gray-900 font-bold text-[10px]">
                    <th>Nilai Akhir</th>
                    <th class="text-amber-600">Target</th>
                    <th class="text-blue-600">Mandiri</th>
                    <th class="text-emerald-600">Verifikator</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-green-100 dark:bg-green-900/50 font-black border-t-2 border-green-600">
                    <td class="text-left pl-4 uppercase tracking-wider">INDEKS SPBE</td>
                    <td>{{ number_format($spbeLama, 2) }}</td>
                    <td class="text-amber-700">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</td>
                    <td class="text-blue-700">{{ number_format($indeksSekarang->indeks_spbe ?? 0, 2) }}</td>
                    <td class="text-center text-emerald-700">{{ number_format($indeksSekarang->indeks_verif ?? 0, 2) }}</td>
                </tr>

               @foreach($allDomainsList as $dom)
    @php
        $curDom = $domainData[$dom->id_domain] ?? null;
        $oldDom = $domainDataLalu[$dom->nama_domain] ?? null;
        $valDomLalu = ($oldDom->domain_verif ?? 0) > 0 ? $oldDom->domain_verif : ($oldDom->nilai_domain ?? 0);
    @endphp
                    <tr class="bg-orange-50 dark:bg-orange-900/40 font-bold border-t border-orange-200">
                        <td class="text-left pl-6 uppercase text-slate-700">Domain {{ $loop->iteration }}: {{ $dom->nama_domain }}</td>
                        <td>{{ number_format($valDomLalu, 2) }}</td>
                        <td class="text-amber-700">{{ number_format($curDom->target ?? 0, 2) }}</td>
                        <td class="text-blue-700">{{ number_format($curDom->nilai_domain ?? 0, 2) }}</td>
                        <td class="text-emerald-700 font-black">{{ number_format($curDom->domain_verif ?? 0, 2) }}</td>
                    </tr>

                    @if(isset($allAspeks[$dom->id_domain]))
@foreach($allAspeks[$dom->id_domain] as $aspek)
    @php
        $curAsp = $aspekData[$aspek->id_aspek] ?? null;
        $oldAsp = $aspekDataLalu[$aspek->nama_aspek] ?? null;
        $valAspLalu = ($oldAsp->aspek_verif ?? 0) > 0 ? $oldAsp->aspek_verif : ($oldAsp->nilai_aspek ?? 0);
    @endphp
                            <tr class="bg-blue-50 dark:bg-blue-900/20 font-bold border-t border-blue-100">
                                <td class="text-left pl-10 text-blue-800 italic">Aspek {{ $aspekCounter++ }}: {{ $aspek->nama_aspek }}</td>
                                <td>{{ number_format($valAspLalu, 2) }}</td>
                                <td class="text-amber-700">{{ number_format($curAsp->target ?? 0, 2) }}</td>
                                <td class="text-blue-700">{{ number_format($curAsp->nilai_aspek ?? 0, 2) }}</td>
                                <td class="text-emerald-700">{{ number_format($curAsp->aspek_verif ?? 0, 2) }}</td>
                            </tr>

                            @php
                                $indikators = \DB::table('indikator')->where('id_aspek', $aspek->id_aspek)->where('tahun', $tahun)->orderBy('urutan', 'asc')->get();
                            @endphp
                           @foreach($indikators as $ind)
    @php
        $det = \DB::table('penilaian_kriteria')->where('id_indikator', $ind->id_indikator)->where('tahun', $tahun)->first();
        
        $indLalu = \DB::table('indikator')
                    ->where('nama_indikator', $ind->nama_indikator)
                    ->where('tahun', $tahunLalu)
                    ->first();
        
        $detLalu = null;
        if($indLalu) {
            $detLalu = \DB::table('penilaian_kriteria')
                        ->where('id_indikator', $indLalu->id_indikator)
                        ->where('tahun', $tahunLalu)
                        ->first();
        }
        
        $mandiriInd = $det->nilai_asesor_internal ?? 0;
        $verifInd = $det->nilai_verifikator_internal ?? 0;
        $valIndLalu = ($detLalu->nilai_verifikator_internal ?? 0) > 0 ? $detLalu->nilai_verifikator_internal : ($detLalu->nilai_asesor_internal ?? 0);
    @endphp
                                <tr class="hover:bg-gray-50 transition-colors bg-white">
                                    <td class="text-left pl-14 text-gray-500 py-3">
                                        Indikator {{ $indikatorCounter++ }}: {{ $ind->nama_indikator }}
                                    </td>
                                    <td>{{ number_format($valIndLalu, 2) }}</td>
                                    <td class="font-bold text-amber-600">{{ number_format($det->nilai_target ?? 0, 2) }}</td>
                                    <td class="text-blue-600">{{ number_format($mandiriInd, 2) }}</td>
                                    <td class="font-black text-emerald-700 bg-emerald-50/20">{{ number_format($verifInd, 2) }}</td>
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