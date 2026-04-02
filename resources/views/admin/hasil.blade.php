@extends('admin.layouts.app')

<link rel="icon" type="image/x-icon" href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
<title>Hasil Penilaian SPBE | {{ $tahunDipilih }}</title>

@section('content')
@section('content')
@php
    $tahun = $tahunDipilih ?? date('Y');
    $tahunLalu = is_numeric($tahun) ? ($tahun - 1) : (date('Y') - 1);
    $indeksSekarang = \DB::table('hasil_indeks')->where('tahun', $tahun)->first();
    $indeksLalu = \DB::table('hasil_indeks')->where('tahun', $tahunLalu)->first();
    $domainHasil = \DB::table('domain_hasil')->where('tahun', $tahun)->get()->keyBy('id_domain');
    $aspekHasil = \DB::table('aspek_hasil')->where('tahun', $tahun)->get()->keyBy('id_aspek');
    $domainHasilLalu = \DB::table('domain_hasil')->where('tahun', $tahunLalu)->get()->keyBy('id_domain');
    $aspekHasilLalu = \DB::table('aspek_hasil')->where('tahun', $tahunLalu)->get()->keyBy('id_aspek');
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="rounded-3xl shadow-xl p-8 bg-white border border-slate-100 flex flex-col justify-center text-center relative overflow-hidden group hover:border-blue-200 transition-all duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <i class="fas fa-bullseye text-6xl text-blue-600"></i>
        </div>
        <h5 class="text-xs font-bold text-slate-400 uppercase mb-2 tracking-widest relative z-10">Target Indeks {{ $tahun }}</h5>
        <p class="text-6xl font-black text-blue-600 mb-2 relative z-10">{{ number_format($indeksSekarang->target_spbe ?? 0, 2) }}</p>
        <div class="px-4 relative z-10">
            <div class="w-full bg-slate-100 h-2.5 rounded-full mt-4 overflow-hidden border border-slate-50">
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-full shadow-[0_0_10px_rgba(37,99,235,0.3)] transition-all duration-1000" 
                     style="width: {{ (($indeksSekarang->target_spbe ?? 0)/5)*100 }}%">
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 font-semibold">Skala Maksimal 5.00</p>
        </div>
    </div>

    <div class="lg:col-span-2 rounded-3xl shadow-blue-200 shadow-2xl p-8 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 text-white relative overflow-hidden group flex items-center">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-blue-400/20 rounded-full blur-2xl"></div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-8 w-full relative z-10">
            <div class="flex-1 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-blue-100 text-[10px] font-bold uppercase tracking-widest mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Hasil Penilaian Akhir
                </div>
                
                <div class="flex items-center justify-center md:justify-start gap-5">
                    <span class="text-8xl font-black tracking-tighter leading-none">{{ number_format($spbeSekarang, 2) }}</span>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center text-sm font-bold {{ $selisih >= 0 ? 'bg-emerald-400 text-emerald-950' : 'bg-rose-400 text-rose-950' }} px-3 py-1 rounded-xl shadow-lg">
                            {!! $selisih >= 0 ? '▲' : '▼' !!} {{ abs(number_format($selisih, 2)) }}
                        </div>
                        <span class="text-xs font-medium text-blue-200 uppercase tracking-tighter">Vs Tahun Lalu</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <span class="px-5 py-2 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-sm font-black uppercase tracking-widest">
                        Predikat: {{ $indeksSekarang->predikat ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <div class="relative w-44 h-44 flex items-center justify-center bg-white/5 rounded-full p-4 border border-white/10 backdrop-blur-sm shadow-inner">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="88" cy="88" r="78" stroke="currentColor" stroke-width="6" fill="transparent" class="text-white/10" />
                    <circle cx="88" cy="88" r="78" stroke="currentColor" stroke-width="10" fill="transparent" 
                        stroke-dasharray="490" 
                        style="stroke-dashoffset: {{ 490 - (490 * ($spbeSekarang / 5)) }}; transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);"
                        class="text-emerald-400" stroke-linecap="round" />
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-3xl font-black">{{ round(($spbeSekarang/5)*100) }}%</span>
                    <span class="text-[10px] uppercase font-bold text-blue-200">Progress</span>
                </div>
            </div>
        </div>
    </div>

</div>

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full table-custom text-[11px]">
                <thead>
                    <tr class="text-center font-bold">
                        <th rowspan="2" class="p-4 text-left bg-slate-50 sticky-col min-w-[300px]">Domain / Aspek / Indikator</th>
                        <th colspan="5" class="bg-slate-100 border-b-2 border-slate-300">Penilaian Tahun {{ $tahunLalu }}</th>
                        <th colspan="5" class="bg-blue-50 border-b-2 border-blue-300">Penilaian Tahun {{ $tahun }}</th>
                    </tr>
                    <tr class="text-[10px] uppercase tracking-tighter bg-slate-50">
                        <th class="p-2 w-16">Target</th>
                        <th class="p-2 w-16">Mandiri</th>
                        <th class="p-2 w-16">Nilai akhir</th>
                        <th class="p-2 w-16">Asesor External</th>
                        <th class="p-2 w-16 font-bold text-blue-700 bg-slate-100">Nilai Akhir External</th>
                        <th class="p-2 w-16 text-amber-600">Target</th>
                        <th class="p-2 w-16 text-blue-600">Mandiri</th>
                        <th class="p-2 w-16 text-indigo-600">Nilai akhir</th>
                        <th class="p-2 w-16 text-purple-600">Asesor External</th>
                        <th class="p-2 w-16 font-bold text-white bg-blue-600">Nilai Akhir External</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200" x-data="{ 
    openDomains: [], 
    openAspeks: [],
    toggleDomain(id) {
        if (this.openDomains.includes(id)) {
            this.openDomains = this.openDomains.filter(i => i !== id);
        } else {
            this.openDomains.push(id);
        }
    },
    toggleAspek(id) {
        if (this.openAspeks.includes(id)) {
            this.openAspeks = this.openAspeks.filter(i => i !== id);
        } else {
            this.openAspeks.push(id);
        }
    }
}">
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
        $oldDom = \DB::table('domain_hasil')
            ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
            ->where('domain.nama_domain', $dom->nama_domain)
            ->where('domain_hasil.tahun', $tahunLalu)
            ->first();
    @endphp
        <tr class="bg-slate-100/80 font-bold text-slate-800 cursor-pointer hover:bg-slate-200"
            @click="toggleDomain({{ $dom->id_domain }})">
            <td class="p-3 pl-6 sticky-col">
                <i class="fas fa-chevron-right mr-2 text-blue-600 transition-transform duration-200"
                   :class="openDomains.includes({{ $dom->id_domain }}) ? 'rotate-90' : ''"></i>
                DOMAIN {{ $loop->iteration }}: {{ $dom->nama_domain }}
            </td>
            <td class="text-center">{{ number_format($oldDom->target ?? 0, 2) }}</td>
            <td class="text-center">{{ number_format($oldDom->nilai_domain ?? 0, 2) }}</td>
            <td class="text-center">{{ number_format($oldDom->domain_verif ?? 0, 2) }}</td>
            <td class="text-center">{{ number_format($oldDom->domain_eksternal ?? 0, 2) }}</td>
            <td class="text-center bg-slate-200 text-blue-700">{{ number_format($oldDom->domain_akhir_eksternal ?? 0, 2) }}</td>
            <td class="text-center text-amber-700">{{ number_format($curDom->target ?? 0, 2) }}</td>
            <td class="text-center text-blue-700">{{ number_format($curDom->nilai_domain ?? 0, 2) }}</td>
            <td class="text-center text-indigo-700">{{ number_format($curDom->domain_verif ?? 0, 2) }}</td>
            <td class="text-center text-purple-700">{{ number_format($curDom->domain_eksternal ?? 0, 2) }}</td>
            <td class="text-center bg-blue-100 text-blue-900">{{ number_format($curDom->domain_akhir_eksternal ?? 0, 2) }}</td>
        </tr>

        @if(isset($allAspeks[$dom->id_domain]))
           @foreach($allAspeks[$dom->id_domain] as $aspek)
    @php
        $curAsp = $aspekHasil[$aspek->id_aspek] ?? null;
        $oldAsp = \DB::table('aspek_hasil')
            ->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
            ->where('aspek.nama_aspek', $aspek->nama_aspek)
            ->where('aspek_hasil.tahun', $tahunLalu)
            ->first();
    @endphp
                
                <tr class="bg-white hover:bg-slate-50 cursor-pointer border-l-4 border-blue-400"
                    x-show="openDomains.includes({{ $dom->id_domain }})"
                    @click="toggleAspek({{ $aspek->id_aspek }})">
                    
                    <td class="p-2 pl-12 font-semibold text-slate-700 sticky-col">
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

                @php
                    $indikators = \DB::table('indikator')->where('id_aspek', $aspek->id_aspek)->where('tahun', $tahun)->orderBy('urutan', 'asc')->get();
                @endphp

                @foreach($indikators as $ind)
                    @php
                        $det = \DB::table('penilaian_kriteria')->where('id_indikator', $ind->id_indikator)->where('tahun', $tahun)->first();
                        $indLalu = \DB::table('indikator')->where('nama_indikator', $ind->nama_indikator)->where('tahun', $tahunLalu)->first();
                        $detLalu = $indLalu ? \DB::table('penilaian_kriteria')->where('id_indikator', $indLalu->id_indikator)->where('tahun', $tahunLalu)->first() : null;
                    @endphp
                    
                    <tr class="bg-slate-50/50 group" 
                        x-show="openDomains.includes({{ $dom->id_domain }}) && openAspeks.includes({{ $aspek->id_aspek }})">
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
    
    <div class="mt-8 bg-blue-50 border border-blue-100 p-4 rounded-2xl">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs text-blue-800 leading-relaxed font-medium">
                <strong>Catatan:</strong> Nilai "Akhir" diambil secara berjenjang dari Verifikator Eksternal, jika kosong maka Verifikator Internal, dan terakhir Penilaian Mandiri. Angka pada Indikator merupakan nilai bulat (level), sedangkan pada Domain/Aspek/Indeks merupakan nilai rata-rata tertimbang.
            </p>
        </div>
    </div>
</div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.min.js"></script>
@endsection