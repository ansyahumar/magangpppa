@extends('layouts.p2')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
     <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Form Penilaian SPBE 
                @if(Auth::user()->role === 'p2') <span class="text-amber-600">(Target P2)</span> @endif
            </h2>
        </div>
        <form method="get" action="{{ route('p2.target') }}" class="bg-white dark:bg-gray-800 p-2 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-300 ml-2">Tahun</label>
                <select name="tahun" id="global-select-tahun" class="form-select border-none focus:ring-0 text-sm font-semibold bg-transparent text-gray-900 dark:text-white cursor-pointer" onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                        @php
                            $checkFinal = in_array($year, $finalizedYears ?? []); 
                            $statusLabel = $checkFinal ? 'Sudah Dinilai' : 'Belum Dinilai';
                        @endphp
                        <option value="{{ $year }}" {{ ($tahun == $year) ? 'selected' : '' }}>
                            {{ $year }} ({{ $statusLabel }})
                        </option>
                    @endforeach
                </select>
                <div class="h-6 w-[1px] bg-gray-200 dark:bg-gray-600"></div>
                <div class="px-3">
                    @php $currentYearIsFinal = in_array($tahun, $finalizedYears ?? []); @endphp
                    @if ($currentYearIsFinal)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50">Terkunci</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50">Terbuka</span>
                    @endif
                </div>
            </div>
        </form>
    </div>

     @php $globalIndikatorCount = 1; @endphp

@php 
    $globalIndikatorCount = 1; 
    $globalAspekCount = 1;
@endphp

@foreach($domains as $d)
    <div class="mb-8 overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md font-bold uppercase tracking-wider">
            Domain {{ $loop->iteration }}: {{ $d->nama_domain }}
        </div>
        
        <div class="p-6 space-y-8">
            @foreach($d->aspek as $a)
                <div>
                    <h4 class="mb-4 text-md font-bold text-gray-700 dark:text-gray-200 flex items-center">
                        <span class="w-2 h-6 bg-blue-500 rounded-full mr-3"></span>
                        Aspek {{ $globalAspekCount++ }}: {{ $a->nama_aspek }} 
                    </h4>
                    
                    <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-16">No</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-left">Indikator Penilaian</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-32">Target Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100">
                                @foreach($a->indikator as $ind)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 text-center text-sm text-gray-400 font-bold">
                                            {{ $globalIndikatorCount }}
                                        </td>
                                        
                                        <td class="px-4 py-4">
                                            <button type="button" class="text-left font-medium text-blue-600 indikator-item hover:underline" 
                                                data-id="{{ $ind->id_indikator }}" 
                                                data-nomor="{{ $globalIndikatorCount++ }}"> 
                                                {{ $ind->nama_indikator }}
                                            </button>
                                        </td>
                                        
                                        <td class="px-4 py-4 text-center">
                                            @php $nilaiTampil = $draft[$ind->id_indikator] ?? 0; @endphp
                                            @if($nilaiTampil > 0)
                                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-amber-600 text-white text-sm font-bold shadow-sm">
                                                    {{ (int)$nilaiTampil }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Belum ada target</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
  <div class="flex flex-col sm:flex-row justify-end items-center gap-4 mt-10 pb-12 border-t pt-8">

@php 
    $totalIndikator = $domains->flatMap->aspek->flatMap->indikator->count();
    $terisiIndikator = count($draft);
    $isFinalized = in_array($tahun, $finalizedYears ?? []); 
@endphp


@if (!$isFinalized || ($terisiIndikator < $totalIndikator))
    <form method="POST" action="{{ route('target.finalisasi') }}" id="form-finalisasi" 
          data-total="{{ $totalIndikator }}" 
          data-terisi="{{ $terisiIndikator }}">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <button type="button" 
                onclick="confirmFinalisasi()"
                class="inline-flex items-center px-8 py-3 font-bold rounded-2xl shadow-lg transition-all duration-200 ease-in-out bg-gradient-to-r from-green-600 to-emerald-600 hover:-translate-y-1 hover:shadow-emerald-500/40 active:scale-95 text-white">
            Finalisasi Target Tahun {{ $tahun }}
        </button>
    </form>
@else
   
    <div class="flex items-center gap-3 px-6 py-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-2xl border border-red-200 dark:border-red-800/30 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <div class="flex flex-col text-left">
            <span class="text-sm font-bold uppercase tracking-tight">Status: Terkunci</span>
            <span class="text-xs opacity-80">Data tahun {{ $tahun }} sudah difinalisasi secara permanen.</span>
        </div>
    </div>
@endif
</div>
</div>

<div id="modal-kriteria" class="fixed inset-0 hidden z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-6xl w-full flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b dark:border-gray-700 text-gray-800 dark:text-white">
            <div>
                <h3 class="text-xl font-bold">Detail Penilaian</h3>
                <p id="modal-subtitle" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <button id="close-modal" class="p-2 bg-gray-100 rounded-full dark:text-black font-bold">✕</button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar" id="kriteria-content"></div>
        <div class="px-6 py-4 border-t dark:border-gray-700 bg-gray-50 text-right">
            <button type="button" id="save-kriteria" class="hidden px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition-all">Simpan Penilaian</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-kriteria');
    const content = document.getElementById('kriteria-content');
    const saveBtn = document.getElementById('save-kriteria');
    const selectTahun = document.getElementById('global-select-tahun');
    const userRole = "{{ Auth::user()->role }}";

    let buktiStore = {}; 
    let activeIndikatorId = null;

    async function showModal(indikatorId, nomorUrut) {
        activeIndikatorId = indikatorId;
        const tahunAktifVal = selectTahun.value || new Date().getFullYear();

        modal.classList.remove('hidden');
        content.innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        saveBtn.classList.add('hidden');
        
        try {
            const res = await fetch(`/indikator/${indikatorId}/detail?tahun=${tahunAktifVal}`);
            const data = await res.json();

            if (!data.kriteria || data.kriteria.length === 0) {
                content.innerHTML = '<div class="p-8 text-center text-gray-500 font-semibold">Data kriteria tidak ditemukan.</div>';
                return;
            }

            const isModeHistori = data.mode === 'histori';
            const isP2Filled = data.kriteria.some(k => k.nilai_target != null && k.nilai_target != 0);
            const isAsesorFilled = data.kriteria.some(k => k.nilai_asesor_internal != null && k.nilai_asesor_internal != 0);
            const isVerifFilled = data.kriteria.some(k => k.nilai_verifikator_internal != null && k.nilai_verifikator_internal != 0);

            if (userRole === 'p2') {
                if (!isP2Filled) saveBtn.classList.remove('hidden');
            } else if (userRole === 'verifikator') {
                if (isModeHistori && !isVerifFilled) saveBtn.classList.remove('hidden');
            } else if (userRole === 'user') {
                if (!isModeHistori && !isAsesorFilled) saveBtn.classList.remove('hidden');
            }

            renderUI(data, isModeHistori, isVerifFilled, isP2Filled, isAsesorFilled, nomorUrut);
        } catch (e) {
            Swal.fire('Error', e.message, 'error');
            modal.classList.add('hidden');
        }
    }

    function renderUI(data, isModeHistori, isVerifFilled, isP2Filled, isAsesorFilled, nomorUrut) {
        const detail = data.detail || { nomor_indikator: '-', nama_indikator: '-' };
        const selectTahun = document.getElementById('global-select-tahun');
        const tahunHistori = data.tahun_histori || (selectTahun.value - 1);
        const kriteriaList = data.kriteria || [];
        const cat = (data.catatan && data.catatan.length > 0) ? data.catatan[0] : {id_catatan: 'new', nama_catatankriteria: '', pencapaian: '', bukti: '[]'};
        const isVerifRole = (userRole === 'verifikator');
        const isP2Role = (userRole === 'p2');
        const isUserRole = (userRole === 'user');
        const lockNotes = (isVerifRole || isP2Role || (isUserRole && (isModeHistori || isAsesorFilled))) ? 'readonly' : '';
        const levelHistori      = kriteriaList.find(k => k.nilai_histori != null)?.nilai_histori;
        const levelTarget       = kriteriaList.find(k => k.nilai_target != null)?.nilai_target;
        const levelAsesorInt    = kriteriaList.find(k => k.nilai_asesor_internal != null)?.nilai_asesor_internal;
        const levelVerifInt     = kriteriaList.find(k => k.nilai_verifikator_internal != null)?.nilai_verifikator_internal;
        const levelAsesorExt    = kriteriaList.find(k => k.nilai_asesor_external != null)?.nilai_asesor_external;
        const levelAkhirExt     = kriteriaList.find(k => k.nilai_akhir_external != null)?.nilai_akhir_external;

        let html = `
            <div class="mb-6 p-5 bg-blue-50 border-l-4 border-blue-600 rounded-r-2xl text-left">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded uppercase tracking-wider">${detail.nama_domain || 'Domain'}</span>
                            <span class="px-2 py-0.5 bg-white text-blue-600 border border-blue-200 text-[10px] font-bold rounded uppercase tracking-wider">${detail.nama_aspek || 'Aspek'}</span>
                        </div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Indikator ${nomorUrut}</p>
                        <h4 class="text-lg font-bold text-gray-900">${detail.nama_indikator}</h4>
                    </div>
                    <div class="flex gap-2 w-full md:w-auto">
                        <a href="/panduan/${activeIndikatorId}/penjelasan" target="_blank" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-blue-50 text-blue-700 rounded-lg border border-blue-200 transition font-bold text-xs shadow-sm">Penjelasan</a>
                        <a href="/panduan/${activeIndikatorId}/penulisan" target="_blank" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200 transition font-bold text-xs shadow-sm">Tata Cara</a>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
                <table class="min-w-full text-sm text-left divide-y divide-gray-200">
                    <thead class="bg-gray-50 font-bold text-center">
                        <tr>
                            <th rowspan="3" class="px-4 py-3 border-r w-12 text-center">Lv</th>
                            <th rowspan="3" class="px-4 py-3 border-r min-w-[300px] text-left">Kriteria Penilaian</th>
                            <th class="px-4 py-2 border-b border-r bg-gray-100">Tahun ${tahunHistori}</th>
                            <th colspan="5" class="px-4 py-2 border-b">Tahun Aktif (${selectTahun.value})</th>
                        </tr>
                        <tr class="text-[10px] uppercase">
                            <th class="border-r">Nilai Akhir</th>
                            <th class="px-4 py-2 border-r ${isP2Role ? 'bg-amber-100' : ''}">Target</th>
                            <th colspan="2" class="px-4 py-2 bg-blue-50 text-blue-700 font-black border-r">Internal</th>
                            <th colspan="2" class="px-4 py-2 bg-amber-50 text-amber-700 font-black">Eksternal</th>
                        </tr>
                        <tr class="text-[10px] uppercase text-center">
                            <th class="border-r"></th>
                            <th class="px-2 py-2 border-r">P2</th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th class="px-2 py-2 border-r">Verif</th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">`;

        kriteriaList.forEach((k, i) => {
            const level = (i % 5) + 1;
            const disTarget   = (isP2Role && !isP2Filled) ? '' : 'disabled';
            const disAsesor   = (isUserRole && !isModeHistori && !isAsesorFilled) ? '' : 'disabled';
            const disVerif    = (isVerifRole && isModeHistori && !isVerifFilled) ? '' : 'disabled';

            html += `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="text-center font-bold text-gray-500 border-r bg-gray-50/50">${level}</td>
                    <td class="p-4 text-xs leading-relaxed border-r">${k.nama_kriteria}</td>
                    <td class="text-center border-r bg-gray-50/30"><input type="checkbox" ${levelHistori == level ? 'checked' : ''} disabled class="rounded w-4 h-4 text-gray-400"></td>
                    <td class="text-center border-r ${isP2Role ? 'bg-amber-50/50' : 'bg-yellow-50/30'}"><input type="checkbox" class="kriteria-cb" ${levelTarget == level ? 'checked' : ''} ${disTarget} data-kriteria="${k.id_kriteria}" data-field="nilai_target" value="${level}"></td>
                    <td class="text-center border-r bg-blue-50/10"><input type="checkbox" class="kriteria-cb" ${levelAsesorInt == level ? 'checked' : ''} ${disAsesor} data-kriteria="${k.id_kriteria}" data-field="nilai_asesor_internal" value="${level}"></td>
                    <td class="text-center border-r bg-blue-50/20"><input type="checkbox" class="kriteria-cb" ${levelVerifInt == level ? 'checked' : ''} ${disVerif} data-kriteria="${k.id_kriteria}" data-field="nilai_verifikator_internal" value="${level}"></td>
                    <td class="text-center border-r bg-amber-50/10"><input type="checkbox" ${levelAsesorExt == level ? 'checked' : ''} disabled class="rounded w-4 h-4"></td>
                    <td class="text-center bg-amber-50/10"><input type="checkbox" ${levelAkhirExt == level ? 'checked' : ''} disabled class="rounded w-4 h-4"></td>
                </tr>`;
        });

        html += `</tbody></table></div>`;
        html += `
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-left">
                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">Catatan & Bukti Pendukung</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Catatan</label>
                        <textarea class="catatan-input w-full border-gray-200 rounded-lg text-xs" rows="4" data-id="${cat.id_catatan}" ${lockNotes}>${cat.nama_catatankriteria ?? ''}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lampiran Bukti</label>
                        ${lockNotes === '' ? `
                            <div class="space-y-2">
                                <input type="file" class="bukti-file block w-full text-[10px] text-gray-500" multiple>
                                <input type="text" placeholder="Tempel Link URL..." class="bukti-link w-full border-gray-200 rounded-lg text-[10px] py-1">
                                <button type="button" class="btn-add-bukti w-full py-1.5 bg-gray-800 text-white rounded-lg text-[10px] font-bold" data-id="${cat.id_catatan}">+ Hubungkan Bukti</button>
                            </div>` : '<p class="text-[10px] text-gray-400 italic">Bukti dikunci (Read-Only).</p>'}
                        <div id="display-${cat.id_catatan}" class="mt-2 flex flex-wrap gap-1"></div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Analisis Pencapaian</label>
                        <textarea class="pencapaian-input w-full border-gray-200 rounded-lg text-xs" rows="4" data-id="${cat.id_catatan}" ${lockNotes}>${cat.pencapaian ?? ''}</textarea>
                    </div>
                </div>
            </div>`;

        content.innerHTML = html;

try {
    const buktiArray = JSON.parse(cat.bukti || '[]');
    const displayDiv = document.getElementById(`display-${cat.id_catatan}`);

    if (buktiArray.length > 0 && displayDiv) {

        buktiArray.forEach((b, index) => {

            if (!b) return;

            let value = b.trim();
            let href = '';
            let isUrl = false;

            const looksLikeUrl =
                value.startsWith('http://') ||
                value.startsWith('https://') ||
                value.startsWith('www.') ||
                (value.includes('.') && !value.toLowerCase().endsWith('.pdf'));

            if (looksLikeUrl) {

                if (!value.startsWith('http://') && !value.startsWith('https://')) {
                    value = 'https://' + value;
                }

                href = value;
                isUrl = true;

            } else {

                const fileName = value.split('/').pop();
                href = `/view-bukti/${encodeURIComponent(fileName)}`;
                isUrl = false;
            }

            const icon = isUrl ? 'fa-link text-emerald-500' : 'fa-file-pdf text-blue-500';
            const label = isUrl ? 'LINK BUKTI' : 'FILE BUKTI';

            displayDiv.innerHTML += `
                <a href="${href}" target="_blank"
                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg border text-[10px] font-bold shadow-sm
                   ${isUrl ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-blue-50 border-blue-200 text-blue-700'}">
                    <i class="fa-solid ${icon} mr-1"></i> ${label} ${index + 1}
                </a>
            `;
        });

    }

} catch (e) {
    console.error('Gagal parse bukti:', e);
}
        content.querySelectorAll('.kriteria-cb').forEach(cb => {
            cb.onchange = function() {
                if (this.checked) {
                    const field = this.dataset.field;
                    content.querySelectorAll(`.kriteria-cb[data-field="${field}"]`).forEach(other => {
                        if (other !== this) other.checked = false;
                    });
                }
            };
        });
    }

saveBtn.onclick = async () => {
    let targetField = '';
    if (userRole === 'p2') targetField = 'nilai_target';
    else if (userRole === 'user') targetField = 'nilai_asesor_internal';
    else if (userRole === 'verifikator') targetField = 'nilai_verifikator_internal';

    const checked = content.querySelector(`.kriteria-cb[data-field="${targetField}"]:checked`);
    
    if (!checked) {
        Swal.fire('Peringatan', `Silakan pilih kriteria untuk kolom ${targetField.replace('nilai_', '').replace('_', ' ')} terlebih dahulu!`, 'warning');
        return;
    }

    const result = await Swal.fire({
        title: 'Simpan Penilaian?',
        text: "Data akan diperbarui ke sistem sesuai dengan peran Anda.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan!'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
        
        const fd = new FormData();       
        const kriteria = [{
            kriteria_id: checked.dataset.kriteria,
            [targetField]: checked.value
        }];

        const mapCatatan = {};
        content.querySelectorAll('.catatan-input').forEach(inp => {
            const cid = inp.dataset.id;
            const pencapaianVal = content.querySelector(`.pencapaian-input[data-id="${cid}"]`)?.value || '';
            mapCatatan[cid] = {
                nama_catatankriteria: inp.value,
                pencapaian: pencapaianVal,
                links: [] 
            };
        });

        fd.append('tahun', selectTahun.value);
        fd.append('id_indikator', activeIndikatorId);
        fd.append('kriteria', JSON.stringify(kriteria));
        fd.append('catatan', JSON.stringify(mapCatatan));
        fd.append('role_pengirim', userRole);
        fd.append('is_edit_mode', '0');
        fd.append('bukti_diklik', '0'); 
        fd.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('/penilaian-kriteria/store', { 
                method: 'POST', 
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const responseData = await res.json();

            if (res.ok) {
                Swal.fire('Berhasil!', 'Penilaian telah berhasil disimpan.', 'success').then(() => window.location.reload());
            } else {
                throw new Error(responseData.message || 'Gagal menyimpan.');
            }
        } catch (e) {
            Swal.fire('Gagal', e.message, 'error');
        }
    }
};

   document.querySelectorAll('.indikator-item').forEach(btn => {
    btn.onclick = () => {
        const id = btn.dataset.id;
        const nomor = btn.dataset.nomor;
        showModal(id, nomor);
    };
});

    document.getElementById('close-modal').onclick = () => modal.classList.add('hidden');
});
</script>
<script>
    @if(session('success'))
        Swal.fire('Berhasil!', "{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        Swal.fire('Gagal!', "{{ session('error') }}", 'error');
    @endif
</script>
<script>
function confirmFinalisasi() {
    
    if (window.event) window.event.preventDefault();

    const form = document.getElementById('form-finalisasi');
    const totalIndikator = parseInt(form.getAttribute('data-total'));
    const terisiIndikator = parseInt(form.getAttribute('data-terisi'));

    if (terisiIndikator < totalIndikator) {
        const sisa = totalIndikator - terisiIndikator;
        
        Swal.fire({
            title: 'Data Belum Lengkap!',
            html: `Masih ada <b>${sisa}</b> indikator yang belum diisi.<br><br>` + 
                  `<small class="text-red-500 font-bold">Layar akan mencari indikator yang kosong...</small>`,
            icon: 'error',
            confirmButtonText: 'Cari Indikator',
            allowOutsideClick: false 
        }).then((result) => {
            if (result.isConfirmed) {
               setTimeout(() => {
                    const rows = document.querySelectorAll('tbody tr');
                    let targetRow = null;

                    for (let row of rows) {
                        if (row.innerText.includes('Belum ada target')) {
                            targetRow = row;
                            break;
                        }
                    }

                    if (targetRow) {
                        targetRow.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });

                        targetRow.style.backgroundColor = "#fff7ed";
                        targetRow.style.outline = "4px solid #f59e0b";
                        targetRow.style.transition = "all 0.3s ease";
                        
                        const link = targetRow.querySelector('button');
                        if(link) link.focus({preventScroll: true});

                        setTimeout(() => {
                            targetRow.style.outline = "none";
                            targetRow.style.backgroundColor = "";
                        }, 5000);
                    }
                }, 300);
            }
        });
        return false; 
    }

   
    Swal.fire({
        title: 'Finalisasi Target?',
        text: "Sistem akan menghitung Indeks Target SPBE tahun {{ $tahun }}. Data akan dikunci setelah ini.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Ya, Hitung & Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endsection