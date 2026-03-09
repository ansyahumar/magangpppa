@extends('admin.layouts.app')

<link rel="icon" type="image/x-icon" href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
<title>Hasil Penilaian SPBE | {{ $tahunDipilih }}</title>

@section('content')
@php
    $tahun = $tahunDipilih ?? date('Y');
    $tahunLalu = is_numeric($tahun) ? ($tahun - 1) : (date('Y') - 1);
    
    $indeksSekarang = \DB::table('hasil_indeks')->where('tahun', $tahun)->first();
    $indeksLalu = \DB::table('hasil_indeks')->where('tahun', $tahunLalu)->first();

    $domainHasil = \DB::table('domain_hasil')->where('tahun', $tahun)->get()->keyBy('id_domain');
    $domainHasilLalu = \DB::table('domain_hasil')
                        ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
                        ->where('domain_hasil.tahun', $tahunLalu)
                        ->select('domain_hasil.*', 'domain.nama_domain')
                        ->get()
                        ->keyBy('nama_domain');
    
    $aspekHasil = \DB::table('aspek_hasil')->where('tahun', $tahun)->get()->keyBy('id_aspek');
    $aspekHasilLalu = \DB::table('aspek_hasil')
                        ->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
                        ->where('aspek_hasil.tahun', $tahunLalu)
                        ->select('aspek_hasil.*', 'aspek.nama_aspek')
                        ->get()
                        ->keyBy('nama_aspek');

    $allDomainsList = \DB::table('domain')->where('tahun', $tahun)->orderBy('urutan', 'asc')->get();
    $allAspeks = \DB::table('aspek')->where('tahun', $tahun)->orderBy('urutan', 'asc')->get()->groupBy('id_domain');

    $spbeSekarang = $indeksSekarang->indeks_verif ?? ($indeksSekarang->indeks_spbe ?? 0);
    $spbeLama = $indeksLalu->indeks_verif ?? ($indeksLalu->indeks_spbe ?? 0);
    $selisih = (float)$spbeSekarang - (float)$spbeLama;

    $aspekCounter = 1;
    $indikatorCounter = 1;
@endphp

<style>
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: slideUp 0.6s ease-out forwards; opacity: 0; }
    .table-custom th, .table-custom td { border: 1px solid #d1d5db; padding: 10px 8px; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8 animate-in">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-black uppercase tracking-tighter">Rincian Hasil Penilaian <span class="text-blue-600">SPBE</span></h2>
            <p class="text-sm text-gray-500 italic">Data perbandingan tahun {{ $tahunLalu }} dan {{ $tahun }}</p>
        </div>
        <form method="get" action="{{ route('admin.hasil') }}" class="bg-white p-2 rounded-lg shadow border">
            <label class="text-xs font-bold uppercase text-gray-400 ml-2">Filter Tahun</label>
                <select name="tahun" id="global-select-tahun" class="form-select border-none focus:ring-0 text-sm font-bold bg-transparent cursor-pointer" onchange="this.form.submit()">
                    @php
                        $listTahun = \DB::table('domain')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
                    @endphp
                    @foreach($listTahun as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        <div class="lg:col-span-2 rounded-3xl shadow-2xl p-8 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white relative overflow-hidden group">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex-1">
                    <h5 class="text-indigo-100 text-sm font-bold uppercase tracking-widest mb-2">Indeks SPBE Keseluruhan</h5>
                    <div class="flex items-baseline gap-4">
                        <span class="text-7xl font-black">{{ number_format($spbeSekarang, 2) }}</span>
                        @if($spbeLama > 0)
                            <div class="flex items-center text-sm font-bold {{ $selisih >= 0 ? 'bg-emerald-400 text-emerald-900' : 'bg-rose-400 text-rose-900' }} px-2 py-1 rounded-lg">
                                {!! $selisih >= 0 ? '↑' : '↓' !!} {{ abs(number_format($selisih, 2)) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <span class="px-4 py-1.5 rounded-xl bg-white/20 backdrop-blur-md text-xs font-black uppercase tracking-tighter">
                            Predikat: {{ $indeksSekarang->predikat ?? '-' }}
                        </span>
                    </div>
                </div>
                 <div class="relative w-32 h-32 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/10" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="10" fill="transparent" 
                            stroke-dasharray="364.4" 
                            style="stroke-dashoffset: {{ 364.4 - (364.4 * ($spbeSekarang / 5)) }};"
                            class="text-emerald-400" />
                    </svg>
                    <span class="absolute text-xl font-bold">{{ round(($spbeSekarang/5)*100) }}%</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl shadow-xl p-8 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex flex-col justify-center text-center">
            <h5 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-widest">Target SPBE {{ $tahun }}</h5>
            <p class="text-5xl font-black text-indigo-500 mb-2">
                {{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}
            </p>
            <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mt-2">
                <div class="h-full bg-indigo-500" style="width: {{ (($indeksSekarang->target_spbe ?? 0)/5)*100 }}%"></div>
            </div>
        </div>
    </div>


     <div class="overflow-x-auto shadow-2xl rounded-lg">
        <table class="min-w-full table-custom bg-white dark:bg-gray-800 text-xs">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-900 font-bold uppercase text-center text-gray-700 dark:text-gray-200">
                    <th rowspan="2" class="w-1/3">Domain / Aspek / Indikator</th>
                    <th>Tahun {{ $tahunLalu }}</th>
                    <th colspan="3" class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200">Tahun {{ $tahun }}</th>
                </tr>
                <tr class="bg-gray-100 dark:bg-gray-900 font-bold text-center text-[10px]">
                    <th>Nilai Akhir</th>
                    <th class="text-amber-600">Target</th>
                    <th class="text-blue-600">Mandiri</th>
                    <th class="text-emerald-600">Verifikator</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-green-100 dark:bg-green-900/50 font-black border-t-2 border-green-600">
                    <td class="pl-4 uppercase tracking-wider">INDEKS SPBE</td>
                    <td class="text-center">{{ number_format($spbeLama, 2) }}</td>
                    <td class="text-center text-amber-700">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</td>
                    <td class="text-center text-blue-700">{{ number_format($indeksSekarang->indeks_spbe ?? 0, 2) }}</td>
                    <td class="text-center text-emerald-700">{{ number_format($indeksSekarang->indeks_verif ?? 0, 2) }}</td>
                </tr>

                @foreach($allDomainsList as $dom)
                   @php
    $curDomRes = $domainHasil[$dom->id_domain] ?? null;
    $oldDomRes = $domainHasilLalu[$dom->nama_domain] ?? null;
    $valDomLalu = ($oldDomRes->domain_verif ?? 0) > 0 ? $oldDomRes->domain_verif : ($oldDomRes->nilai_domain ?? 0);
@endphp
                    <tr class="bg-orange-50 dark:bg-orange-900/30 font-bold border-t border-orange-200">
                        <td class="pl-6 uppercase">Domain {{ $loop->iteration }}: {{ $dom->nama_domain }}</td>
                        <td class="text-center font-medium">{{ number_format($valDomLalu, 2) }}</td>
                        <td class="text-center text-amber-700">{{ number_format($curDomRes->target ?? 0, 2) }}</td>
                        <td class="text-center text-blue-700">{{ number_format($curDomRes->nilai_domain ?? 0, 2) }}</td>
                        <td class="text-center text-emerald-700">{{ number_format($curDomRes->domain_verif ?? 0, 2) }}</td>
                    </tr>

                    @if(isset($allAspeks[$dom->id_domain]))
                        @foreach($allAspeks[$dom->id_domain] as $aspek)
                            @php
    $curAspRes = $aspekHasil[$aspek->id_aspek] ?? null;
    $oldAspRes = $aspekHasilLalu[$aspek->nama_aspek] ?? null;
    $valAspLalu = ($oldAspRes->aspek_verif ?? 0) > 0 ? $oldAspRes->aspek_verif : ($oldAspRes->nilai_aspek ?? 0);
@endphp
                            <tr class="bg-blue-50 dark:bg-blue-900/20 font-bold border-t border-blue-100">
                                <td class="pl-10 text-blue-800">Aspek {{ $aspekCounter++ }}: {{ $aspek->nama_aspek }}</td>
                                <td class="text-center font-medium">{{ number_format($valAspLalu, 2) }}</td>
                                <td class="text-center text-amber-700">{{ number_format($curAspRes->target ?? 0, 2) }}</td>
                                <td class="text-center text-blue-700">{{ number_format($curAspRes->nilai_aspek ?? 0, 2) }}</td>
                                <td class="text-center text-emerald-700">{{ number_format($curAspRes->aspek_verif ?? 0, 2) }}</td>
                            </tr>

                            @php
                                $indikators = \DB::table('indikator')
                                    ->where('id_aspek', $aspek->id_aspek)
                                    ->where('tahun', $tahun)
                                    ->orderBy('urutan', 'asc')
                                    ->get();
                            @endphp
                           @foreach($indikators as $ind)
    @php
        $det = \DB::table('penilaian_kriteria')->where('id_indikator', $ind->id_indikator)->where('tahun', $tahun)->first();
        
        $indLalu = \DB::table('indikator')
                    ->where('nama_indikator', $ind->nama_indikator)
                    ->where('tahun', $tahunLalu)
                    ->first();
        
        $valIndLalu = 0;
        if($indLalu) {
            $detLalu = \DB::table('penilaian_kriteria')
                        ->where('id_indikator', $indLalu->id_indikator)
                        ->where('tahun', $tahunLalu)
                        ->first();
            $valIndLalu = ($detLalu->nilai_verifikator_internal ?? 0) > 0 
                          ? $detLalu->nilai_verifikator_internal 
                          : ($detLalu->nilai_asesor_internal ?? 0);
        }
    @endphp
    <tr class="...">
        <td class="...">Indikator {{ $indikatorCounter++ }}: {{ $ind->nama_indikator }}</td>
        <td class="text-center">{{ number_format($valIndLalu, 2) }}</td> 
        <td class="...">{{ number_format($det->nilai_target ?? 0, 2) }}</td>
        <td class="...">{{ number_format($det->nilai_asesor_internal ?? 0, 2) }}</td>
        <td class="...">{{ number_format($det->nilai_verifikator_internal ?? 0, 2) }}</td>
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