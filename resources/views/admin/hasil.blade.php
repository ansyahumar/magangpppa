@extends('admin.layouts.app')

@include('layouts.fav')
<title>Hasil Penilaian SPBE | {{ $tahunDipilih }}</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@section('content')
@php
    $tahun = $tahunDipilih ?? date('Y');
    $tahunLalu = is_numeric($tahun) ? ($tahun - 1) : (date('Y') - 1);
    $indeksSekarang = \DB::table('hasil_indeks')->where('tahun', $tahun)->first();
    $indeksLalu = \DB::table('hasil_indeks')->where('tahun', $tahunLalu)->first();
    $domainHasil = \DB::table('domain_hasil')->where('tahun', $tahun)->get()->keyBy('id_domain');
    $aspekHasil = \DB::table('aspek_hasil')->where('tahun', $tahun)->get()->keyBy('id_aspek');
    $allDomainsList = \DB::table('domain')->where('tahun', $tahun)->orderBy('urutan', 'asc')->get();
    $allAspeks = \DB::table('aspek')->where('tahun', $tahun)->orderBy('urutan', 'asc')->get()->groupBy('id_domain');
    $spbeSekarang = $indeksSekarang->indeks_akhir_eksternal ?? 0;
    $spbeLama = $indeksLalu->indeks_akhir_eksternal ?? 0;
    $selisih = (float)$spbeSekarang - (float)$spbeLama;
    $aspekCounterGlobal = 1;
    $indikatorCounterGlobal = 1;
@endphp

<style>
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: slideUp 0.6s ease-out forwards; opacity: 0; }
    .table-custom th { background-color: #f8fafc; color: #1e3a8a; border: 1px solid #e2e8f0; }
    .table-custom td { border: 1px solid #e2e8f0; vertical-align: middle; }
    .sticky-col { position: sticky; left: 0; background-color: inherit; z-index: 10; }
    .rotate-90 { transform: rotate(90deg); }
</style>

<div class="max-w-full mx-auto px-4 py-8 animate-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Rincian Hasil <span class="text-blue-600">Penilaian SPBE</span></h2>
            <p class="text-sm text-slate-500 font-medium">Perbandingan detail parameter penilaian tahun {{ $tahunLalu }} vs {{ $tahun }}</p>
        </div>
        <form method="get" action="{{ route('admin.hasil') }}" class="flex items-center bg-white shadow-sm border rounded-2xl px-4 py-2">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
            <select name="tahun" onchange="this.form.submit()" class="border-none focus:ring-0 text-sm font-bold text-slate-700 bg-transparent cursor-pointer">
                @php $listTahun = \DB::table('domain')->distinct()->orderBy('tahun', 'desc')->pluck('tahun'); @endphp
                @foreach($listTahun as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="rounded-3xl shadow-xl p-6 bg-white border border-slate-100 flex flex-col justify-center text-center relative overflow-hidden">
            <h5 class="text-[10px] font-bold text-slate-400 uppercase mb-2 tracking-widest">Target {{ $tahun }}</h5>
            <p class="text-4xl font-black text-amber-500 mb-2">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-amber-400 h-full" style="width: {{ (($indeksSekarang->target_spbe ?? 0)/5)*100 }}%"></div>
            </div>
        </div>

        <div class="rounded-3xl shadow-xl p-6 bg-white border border-slate-100 flex flex-col justify-center text-center relative overflow-hidden">
            <h5 class="text-[10px] font-bold text-slate-400 uppercase mb-2 tracking-widest">Indeks Mandiri</h5>
            <p class="text-4xl font-black text-blue-600 mb-2">{{ number_format($indeksSekarang->indeks_spbe ?? 0, 2) }}</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-full" style="width: {{ (($indeksSekarang->indeks_spbe ?? 0)/5)*100 }}%"></div>
            </div>
        </div>

        @php
            $totalIndikator = \DB::table('indikator')->where('tahun', $tahun)->count() ?: 47;
            $terisi = \DB::table('penilaian_kriteria')->where('tahun', $tahun)->whereNotNull('nilai_akhir_external')->count();
            $persenJalan = round(($terisi / $totalIndikator) * 100);
        @endphp
        <div class="rounded-3xl shadow-xl p-6 bg-white border border-slate-100 flex flex-col justify-center text-center relative overflow-hidden group">
            <h5 class="text-[10px] font-bold text-slate-400 uppercase mb-2 tracking-widest">Progres Penilaian </h5>
            <p class="text-4xl font-black text-emerald-600 mb-2">{{ $persenJalan }}%</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div class="bg-emerald-500 h-full" style="width: {{ $persenJalan }}%"></div>
            </div>
        </div>

        <div class="rounded-3xl shadow-2xl p-6 bg-gradient-to-br from-indigo-600 to-blue-800 text-white relative overflow-hidden">
            <div class="text-center h-full flex flex-col justify-center">
                <h5 class="text-[9px] font-bold uppercase tracking-widest mb-2 opacity-80">Indeks Akhir {{ $tahun }}</h5>
                <div class="flex items-center justify-center gap-3">
                    <span class="text-5xl font-black tracking-tighter">{{ number_format($spbeSekarang, 2) }}</span>
                    <span class="text-[10px] font-bold {{ $selisih >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {!! $selisih >= 0 ? '▲' : '▼' !!} {{ abs(number_format($selisih, 2)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full table-custom text-[11px]" x-data="{ 
                openDomains: [], 
                openAspeks: [],
                toggleDomain(id) {
                    this.openDomains.includes(id) ? this.openDomains = this.openDomains.filter(i => i !== id) : this.openDomains.push(id);
                },
                toggleAspek(id) {
                    this.openAspeks.includes(id) ? this.openAspeks = this.openAspeks.filter(i => i !== id) : this.openAspeks.push(id);
                }
            }">
                <thead>
                    <tr class="text-center font-bold">
                        <th rowspan="2" class="p-4 text-left bg-slate-50 sticky-col min-w-[300px]">Domain / Aspek / Indikator</th>
                        <th colspan="5" class="bg-slate-100 border-b-2 border-slate-300">Penilaian Tahun {{ $tahunLalu }}</th>
                        <th colspan="5" class="bg-blue-50 border-b-2 border-blue-300">Penilaian Tahun {{ $tahun }}</th>
                    </tr>
                    <tr class="text-[10px] uppercase tracking-tighter bg-slate-50">
                        <th class="p-2 w-16">Target</th><th class="p-2 w-16">Mandiri</th><th class="p-2 w-16">Verif</th><th class="p-2 w-16">Eks</th><th class="p-2 w-16 font-bold text-blue-700 bg-slate-100">Akhir</th>
                        <th class="p-2 w-16 text-amber-600">Target</th><th class="p-2 w-16 text-blue-600">Mandiri</th><th class="p-2 w-16 text-indigo-600">Verif</th><th class="p-2 w-16 text-purple-600">Eks</th><th class="p-2 w-16 font-bold text-white bg-blue-600">Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr class="bg-blue-700 text-white font-black text-sm">
                        <td class="p-4 sticky-col">INDEKS SPBE KESELURUHAN</td>
                        <td class="text-center">{{ number_format($indeksLalu->target_spbe ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksLalu->indeks_spbe ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksLalu->indeks_verif ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksLalu->indeks_eksternal ?? 0, 2) }}</td>
                        <td class="text-center bg-blue-800">{{ number_format($indeksLalu->indeks_akhir_eksternal ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksSekarang->indeks_spbe ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksSekarang->indeks_verif ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($indeksSekarang->indeks_eksternal ?? 0, 2) }}</td>
                        <td class="text-center bg-blue-900">{{ number_format($indeksSekarang->indeks_akhir_eksternal ?? 0, 2) }}</td>
                    </tr>

                    @foreach($allDomainsList as $dom)
                    @php
                        $curDom = $domainHasil[$dom->id_domain] ?? null;
                        $oldDom = \DB::table('domain_hasil')->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
                                    ->where('domain.nama_domain', $dom->nama_domain)->where('domain_hasil.tahun', $tahunLalu)->first();
                    @endphp
                    <tr class="font-bold text-slate-800 cursor-pointer transition-all duration-200"
                        :class="openDomains.includes({{ $dom->id_domain }}) ? 'bg-blue-50/30' : 'bg-slate-100/80 hover:bg-slate-200'"
                        @click="toggleDomain({{ $dom->id_domain }})">
                        <td class="p-3 pl-6 sticky-col bg-inherit">
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right mr-3 text-sm transition-all duration-300 transform"
                                   :class="openDomains.includes({{ $dom->id_domain }}) ? 'rotate-90 text-blue-600' : 'text-slate-400'"></i>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-widest opacity-50 font-black">Domain {{ $loop->iteration }}</span>
                                    <span class="leading-tight tracking-tighter">{{ $dom->nama_domain }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ number_format($oldDom->target ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($oldDom->nilai_domain ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($oldDom->domain_verif ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($oldDom->domain_eksternal ?? 0, 2) }}</td>
                        <td class="text-center bg-slate-200/30 text-blue-700">{{ number_format($oldDom->domain_akhir_eksternal ?? 0, 2) }}</td>
                        <td class="text-center text-amber-700">{{ number_format($curDom->target ?? 0, 2) }}</td>
                        <td class="text-center text-blue-700 font-bold">{{ number_format($curDom->nilai_domain ?? 0, 2) }}</td>
                        <td class="text-center text-indigo-700">{{ number_format($curDom->domain_verif ?? 0, 2) }}</td>
                        <td class="text-center text-purple-700">{{ number_format($curDom->domain_eksternal ?? 0, 2) }}</td>
                        <td class="text-center bg-blue-100/50 text-blue-900 font-bold">{{ number_format($curDom->domain_akhir_eksternal ?? 0, 2) }}</td>
                    </tr>

                    @if(isset($allAspeks[$dom->id_domain]))
                        @foreach($allAspeks[$dom->id_domain] as $aspek)
                        @php
                            $curAsp = $aspekHasil[$aspek->id_aspek] ?? null;
                            $oldAsp = \DB::table('aspek_hasil')->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
                                        ->where('aspek.nama_aspek', $aspek->nama_aspek)->where('aspek_hasil.tahun', $tahunLalu)->first();
                        @endphp
                        <tr class="bg-white hover:bg-slate-50 cursor-pointer border-l-4 border-blue-400"
                            x-show="openDomains.includes({{ $dom->id_domain }})"
                            @click="toggleAspek({{ $aspek->id_aspek }})">
                            <td class="p-2 pl-12 font-semibold text-slate-700 sticky-col bg-white">
                                <i class="fas fa-caret-right mr-2 text-slate-400 transition-transform duration-200"
                                   :class="openAspeks.includes({{ $aspek->id_aspek }}) ? 'rotate-90' : ''"></i>
                                Aspek {{ $aspekCounterGlobal++ }}: {{ $aspek->nama_aspek }}
                            </td>
                            <td class="text-center text-slate-500">{{ number_format($oldAsp->target ?? 0, 2) }}</td>
                            <td class="text-center text-slate-500">{{ number_format($oldAsp->nilai_aspek ?? 0, 2) }}</td>
                            <td class="text-center text-slate-500">{{ number_format($oldAsp->aspek_verif ?? 0, 2) }}</td>
                            <td class="text-center text-slate-500">{{ number_format($oldAsp->aspek_eksternal ?? 0, 2) }}</td>
                            <td class="text-center bg-slate-50 font-bold text-slate-700">{{ number_format($oldAsp->aspek_akhir_eksternal ?? 0, 2) }}</td>
                            <td class="text-center text-amber-600/80">{{ number_format($curAsp->target ?? 0, 2) }}</td>
                            <td class="text-center text-blue-600/80">{{ number_format($curAsp->nilai_aspek ?? 0, 2) }}</td>
                            <td class="text-center text-indigo-600/80">{{ number_format($curAsp->aspek_verif ?? 0, 2) }}</td>
                            <td class="text-center text-purple-600/80">{{ number_format($curAsp->aspek_eksternal ?? 0, 2) }}</td>
                            <td class="text-center bg-blue-50 font-bold text-blue-700">{{ number_format($curAsp->aspek_akhir_eksternal ?? 0, 2) }}</td>
                        </tr>

                        @php $indikators = \DB::table('indikator')->where('id_aspek', $aspek->id_aspek)->where('tahun', $tahun)->orderBy('urutan', 'asc')->get(); @endphp
                        @foreach($indikators as $ind)
                            @php
                                $det = \DB::table('penilaian_kriteria')->where('id_indikator', $ind->id_indikator)->where('tahun', $tahun)->first();
                                $indLalu = \DB::table('indikator')->where('nama_indikator', $ind->nama_indikator)->where('tahun', $tahunLalu)->first();
                                $detLalu = $indLalu ? \DB::table('penilaian_kriteria')->where('id_indikator', $indLalu->id_indikator)->where('tahun', $tahunLalu)->first() : null;
                            @endphp
                            <tr class="bg-slate-50/50 group" x-show="openDomains.includes({{ $dom->id_domain }}) && openAspeks.includes({{ $aspek->id_aspek }})">
                                <td class="p-2 pl-20 text-slate-500 group-hover:text-blue-600 italic sticky-col bg-slate-50">
                                    <span class="inline-block w-2 h-2 rounded-full bg-slate-300 mr-2"></span>
                                    Indikator {{ $indikatorCounterGlobal++ }}: {{ $ind->nama_indikator }}
                                </td>
                                <td class="text-center text-slate-400">{{ number_format($detLalu->nilai_target ?? 0, 0) }}</td>
                                <td class="text-center text-slate-400">{{ number_format($detLalu->nilai_asesor_internal ?? 0, 0) }}</td>
                                <td class="text-center text-slate-400">{{ number_format($detLalu->nilai_verifikator_internal ?? 0, 0) }}</td>
                                <td class="text-center text-slate-400">{{ number_format($detLalu->nilai_asesor_external ?? 0, 0) }}</td>
                                <td class="text-center bg-slate-100/50 text-slate-500 font-medium">{{ number_format($detLalu->nilai_akhir_external ?? 0, 0) }}</td>
                                <td class="text-center text-amber-500/60">{{ number_format($det->nilai_target ?? 0, 0) }}</td>
                                <td class="text-center text-blue-500/60">{{ number_format($det->nilai_asesor_internal ?? 0, 0) }}</td>
                                <td class="text-center text-indigo-500/60">{{ number_format($det->nilai_verifikator_internal ?? 0, 0) }}</td>
                                <td class="text-center text-purple-500/60">{{ number_format($det->nilai_asesor_external ?? 0, 0) }}</td>
                                <td class="text-center bg-blue-50/80 text-blue-600 font-bold">{{ number_format($det->nilai_akhir_external ?? 0, 0) }}</td>
                            </tr>
                        @endforeach
                        @endforeach
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Alpine === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js";
            script.defer = true;
            document.head.appendChild(script);
        }
    });
</script>
@endsection