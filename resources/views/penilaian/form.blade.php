<link rel="icon" type="image/x-icon"
      href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
    <title>Form Penilaian</title>

<style>
    input[type="checkbox"]:disabled {
        background-color: #e5e7eb !important; 
        border-color: #d1d5db !important;   
        cursor: not-allowed;
        opacity: 0.7;
    }
    input[type="checkbox"]:checked:disabled {
        background-color: #9ca3af !important; 
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
    }
</style>

<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Form Penilaian SPBE
                    @if(Auth::user()->role === 'p2') <span class="text-amber-600">(Target P2)</span> @endif
                </h2>
            </div>
            <form method="get" action="{{ route('penilaian.form') }}" class="bg-white dark:bg-gray-800 p-2 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
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

  @php 
    $globalIndikatorCount = 1; 
    $globalAspekCount = 1;
@endphp

@foreach($domains as $d)
    <div class="mb-8 overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md font-bold">
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
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-left">Indikator</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-32">Nilai Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100">
                                @foreach($a->indikator as $ind)
                                    <tr id="row-{{ $ind->id_indikator }}" class="baris-indikator hover:bg-gray-50 transition-colors border-l-4 border-transparent">
                                        <td class="px-4 py-4 text-center text-sm text-gray-400 font-bold">
                                            {{ $globalIndikatorCount++ }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <button type="button" 
        class="text-left font-medium text-blue-600 hover:underline indikator-item" 
        data-id="{{ $ind->id_indikator }}" 
        data-nomor="{{ $globalIndikatorCount - 1 }}"> 
    {{ $ind->nama_indikator }}
</button>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @php 
                                                $n = $draft->get($ind->id_indikator); 
                                                $v = $n ? $n->nilai : 0; 
                                            @endphp
                                            <span class="nilai-angka inline-flex items-center px-3 py-1 rounded-lg {{ $v > 0 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }} text-sm font-bold shadow-sm">
                                                {{ $v }}
                                            </span>
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

       @if(Auth::user()->role === 'user')
     @if (!$currentYearIsFinal)
        <div class="flex justify-end mt-10 pb-12 border-t pt-8">
            <button type="button" 
                    onclick="finalisasiUser()" 
                    class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-2xl shadow-lg 
                           hover:-translate-y-1 hover:shadow-emerald-200/50 
                           active:scale-90 active:duration-75 
                           transition-all duration-200 ease-in-out">
                
                           Finalisasi Penilaian Tahun {{ $tahun }}
            </button>
        </div>
    @else
        <div class="flex justify-end mt-10 pb-12 border-t pt-8">
            <div class="flex items-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-semibold">penilaian Tahun {{ $tahun }} Telah Dikunci</span>
        </div>
        </div>
    @endif
@endif
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
async function finalisasiUser() {
    try {
        if (document.activeElement) {
            document.activeElement.blur();
        }

        const tahunElement = document.getElementById('global-select-tahun');
        const tahun = tahunElement ? tahunElement.value : '{{ $tahun }}';
        const allRows = document.querySelectorAll('.baris-indikator');
        let firstEmptyRow = null;
        let emptyCount = 0;

        allRows.forEach(row => {
            row.style.backgroundColor = ''; 
            row.classList.remove('ring-4', 'ring-red-500');

            const nilaiSpan = row.querySelector('.nilai-angka');
            const nilai = nilaiSpan ? parseInt(nilaiSpan.innerText.trim()) : 0;

            if (isNaN(nilai) || nilai === 0) {
                emptyCount++;
                if (!firstEmptyRow) firstEmptyRow = row;
                row.style.backgroundColor = 'rgba(254, 226, 226, 0.6)';
            }
        });

        if (emptyCount > 0 && firstEmptyRow) {
    await Swal.fire({
        title: 'Data Belum Lengkap',
        text: `${emptyCount} Indikator masih ada yang belum diisi.`,
        icon: 'warning',
        confirmButtonText: 'Lihat'
    });

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

    setTimeout(() => {
        firstEmptyRow.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        firstEmptyRow.classList.add('ring-4', 'ring-red-500', 'ring-inset');
    }, 500);

    return; 
}

        const konfirmasi = await Swal.fire({
            title: 'Finalisasi?',
            text: "Data yang sudah difinalisasi tidak dapat diubah lagi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses Sekarang',
            cancelButtonText: 'Batal'
        });

        if (konfirmasi.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memproses...',
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false
            });

            const response = await fetch("{{ route('penilaian.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tahun: tahun })
            });

            const contentType = response.headers.get("content-type");
            if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                await Swal.fire('Berhasil!', 'Data telah difinalisasi.', 'success');
                window.location.reload();
            } else if (response.ok) {

                await Swal.fire('Berhasil!', 'Data telah diproses.', 'success');
                window.location.reload();
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Gagal memproses data.');
            }
        }
    } catch (error) {
        console.error("Finalisasi Error:", error);
        Swal.fire('Gagal', error.message || 'Terjadi kesalahan sistem.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-kriteria');
    const content = document.getElementById('kriteria-content');
    const saveBtn = document.getElementById('save-kriteria');
    const selectTahun = document.getElementById('global-select-tahun');
    const tahunAktif = "{{ $tahun }}"; 
    const userRole = "{{ Auth::user()->role }}";

    let buktiStore = {}; 
    let activeIndikatorId = null;
    let buktiClicked = false;

    async function showModal(indikatorId, nomorUrut) {
        activeIndikatorId = indikatorId;
        const tahunAktifVal = document.getElementById('global-select-tahun').value;
        
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        saveBtn.classList.add('hidden');
        buktiStore = {};

        try {
            const res = await fetch(`/indikator/${indikatorId}/detail?tahun=${tahunAktifVal}`);
            if (!res.ok) throw new Error(`Server merespon dengan status ${res.status}`);
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
            console.error(e);
            Swal.fire('Error', e.message, 'error');
            modal.classList.add('hidden');
        }
    }

    function renderUI(data, isModeHistori, isVerifFilled, isP2Filled, isAsesorFilled, nomorUrut) {
        const detail = data.detail || { nomor_indikator: '-', nama_indikator: '-' };
        const tahunHistoriVal = data.tahun_histori || (parseInt(tahunAktif) - 1);
        const cat = (data.catatan && data.catatan.length > 0) ? data.catatan[0] : {id_catatan: 'new', nama_catatankriteria: '', pencapaian: '', bukti: '[]'};
        const isVerifRole = (userRole === 'verifikator');
        const isP2Role = (userRole === 'p2');
        const isUserRole = (userRole === 'user');
        const lockNotes = (isVerifRole || isP2Role || (isUserRole && (isModeHistori || isAsesorFilled))) ? 'readonly' : '';
        const globalTarget = data.kriteria.find(k => Number(k.nilai_target) > 0)?.nilai_target || 0;
        const globalAsesor = data.kriteria.find(k => Number(k.nilai_asesor_internal) > 0)?.nilai_asesor_internal || 0;
        const globalVerif  = data.kriteria.find(k => Number(k.nilai_verifikator_internal) > 0)?.nilai_verifikator_internal || 0;
        const globalHistori = data.kriteria.find(k => Number(k.nilai_histori) > 0)?.nilai_histori || 0;
        const globalAsesorExt = data.kriteria.find(k => Number(k.nilai_asesor_external) > 0)?.nilai_asesor_external || 0;
        const globalVerifExt  = data.kriteria.find(k => Number(k.nilai_akhir_external) > 0)?.nilai_akhir_external || 0;

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
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 mb-6 shadow-sm">
                <table class="min-w-full text-[11px] text-left divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/80 font-bold text-center text-gray-600 dark:text-gray-300">
                        <tr>
                            <th rowspan="3" class="px-4 py-3 border-r w-12 text-center">Lv</th>
                            <th rowspan="3" class="px-4 py-3 border-r min-w-[300px] text-left">Kriteria Penilaian</th>
                            <th class="px-4 py-2 border-b border-r italic">Tahun ${tahunHistoriVal}</th>
                            <th colspan="5" class="px-4 py-2 border-b">Tahun Aktif (${tahunAktif})</th>
                        </tr>
                        <tr class="text-[9px] uppercase tracking-tighter">
                            <th class="border-r bg-gray-100/50">Nilai Akhir</th>
                            <th class="px-4 py-2 border-r">Target</th>
                            <th colspan="2" class="px-4 py-2 bg-blue-50 text-blue-700 border-r">Internal</th>
                            <th colspan="2" class="px-4 py-2 bg-amber-50 text-amber-700">Eksternal</th>
                        </tr>
                        <tr class="text-[8px] uppercase">
                            <th class="border-r bg-gray-100/50"></th>
                            <th class="px-2 py-2 border-r">P2</th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th class="px-2 py-2 border-r">Verif</th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">`;

        data.kriteria.forEach((k, i) => {
            const level = (i % 5) + 1;         
            const chkHistori = Number(globalHistori) === level ? 'checked' : '';
            const chkTarget  = Number(globalTarget) === level ? 'checked' : '';
            const chkAsesor  = Number(globalAsesor) === level ? 'checked' : '';
            const chkVerif   = Number(globalVerif) === level ? 'checked' : ''; 
            const chkAsesorExt = Number(globalAsesorExt) === level ? 'checked' : '';
            const chkAkhirExt  = Number(globalVerifExt) === level ? 'checked' : '';
            const disTarget = (isP2Role && !isP2Filled) ? '' : 'disabled';
            const disAsesor = (isUserRole && !isModeHistori && !isAsesorFilled) ? '' : 'disabled';
            const disVerif  = (isVerifRole && isModeHistori && !isVerifFilled) ? '' : 'disabled';

            html += `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="text-center font-bold text-gray-500 border-r bg-gray-50/50">${level}</td>
                    <td class="p-4 text-xs leading-relaxed border-r">${k.nama_kriteria}</td>
                    <td class="text-center border-r bg-gray-50/30">
                        <input type="checkbox" ${chkHistori} disabled class="rounded w-4 h-4">
                    </td>
                    <td class="text-center border-r">
                        <input type="checkbox" class="kriteria-cb" ${chkTarget} ${disTarget} data-kriteria="${k.id_kriteria}" data-field="nilai_target" value="${level}">
                    </td>
                    <td class="text-center border-r bg-blue-50/20">
                        <input type="checkbox" class="kriteria-cb" ${chkAsesor} ${disAsesor} data-kriteria="${k.id_kriteria}" data-field="nilai_asesor_internal" value="${level}">
                    </td>
                    <td class="text-center bg-blue-50/20 border-r">
                        <input type="checkbox" class="kriteria-cb" ${chkVerif} ${disVerif} data-kriteria="${k.id_kriteria}" data-field="nilai_verifikator_internal" value="${level}">
                    </td>
                    <td class="text-center border-r bg-amber-50/20">
                        <input type="checkbox" ${chkAsesorExt} disabled class="rounded w-4 h-4">
                    </td>
                    <td class="text-center bg-amber-50/20">
                        <input type="checkbox" ${chkAkhirExt} disabled class="rounded w-4 h-4 text-emerald-600">
                    </td>
                </tr>`;
        });

        html += `</tbody></table></div>`;
        html += `
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-left">
                <h4 class="font-bold text-gray-800 mb-4">Catatan & Bukti Pendukung</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Catatan</label>
                        <textarea class="catatan-input w-full border-gray-200 rounded-lg text-xs" rows="4" data-id="${cat.id_catatan}" ${lockNotes}>${cat.nama_catatankriteria ?? ''}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Lampiran Bukti</label>
                        ${lockNotes === '' ? `
                            <div class="space-y-2">
                                <input type="file" class="bukti-file block w-full text-[10px] text-gray-500" multiple>
                                <input type="text" placeholder="Link URL..." class="bukti-link w-full border-gray-200 rounded-lg text-[10px] py-1">
                                <button type="button" class="btn-add-bukti w-full py-1.5 bg-gray-800 text-white rounded-lg text-[10px] font-bold" data-id="${cat.id_catatan}">+ Hubungkan Bukti</button>
                            </div>
                        ` : '<p class="text-[10px] text-gray-400 italic">Bukti dikunci.</p>'}
                        <div id="display-${cat.id_catatan}" class="mt-2 flex flex-wrap gap-1">`;
        
        try {
            const buktiArray = JSON.parse(cat.bukti || '[]');

buktiArray.forEach((b, index) => {
    if (!b) return;

    let value = b.trim();
    let finalUrl = '';
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

        finalUrl = value;
        isUrl = true;

    } else {

        const fileName = value.split('/').pop();

        finalUrl = `/view-bukti/${fileName}`;
        isUrl = false;
    }

    const icon = isUrl 
        ? `<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
             d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826L10.242 9.172a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.102 1.101" />
           </svg>` 
        : `<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
             d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
           </svg>`;

    const theme = isUrl 
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
        : 'bg-blue-50 text-blue-700 border-blue-200';

    const label = isUrl ? `Link ${index + 1}` : `File ${index + 1}`;

    html += `
        <a href="${finalUrl}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="inline-flex items-center px-2.5 py-1.5 rounded-full border ${theme} text-[10px] font-bold transition-all shadow-sm hover:shadow-md">
            ${icon} <span>${label}</span>
        </a>`;
});
        } catch(e) {}

        html += `</div></div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Catatan Verifikator</label>
                        <textarea class="pencapaian-input w-full border-gray-200 rounded-lg text-xs bg-gray-100" rows="4" data-id="${cat.id_catatan}" disabled>${cat.pencapaian ?? ''}</textarea>
                    </div>
                </div>
            </div>`;

        content.innerHTML = html;
        attachInternalListeners();
    }

    function attachInternalListeners() {
        content.querySelectorAll('.kriteria-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    const field = this.dataset.field;
                    content.querySelectorAll(`.kriteria-cb[data-field="${field}"]`).forEach(other => {
                        if (other !== this) other.checked = false;
                    });
                }
            });
        });

        const addBtn = content.querySelector('.btn-add-bukti');
        if (addBtn) {
            addBtn.onclick = function() {
                const catId = this.dataset.id;
                buktiClicked = true;
                const fileInput = content.querySelector('.bukti-file');
                const linkInput = content.querySelector('.bukti-link');
                const displayDiv = document.getElementById(`display-${catId}`);
                if (!buktiStore[catId]) buktiStore[catId] = { files: [], links: [] };

                if (fileInput.files.length > 0) {
                    Array.from(fileInput.files).forEach(file => {
                        buktiStore[catId].files.push(file);
                        const tag = document.createElement('span');
                        tag.className = "px-2 py-1 rounded-full bg-orange-100 text-orange-700 text-[10px]";
                        tag.innerText = "New File";
                        displayDiv.appendChild(tag);
                    });
                    fileInput.value = '';
                }
                if (linkInput.value.trim() !== '') {
                    buktiStore[catId].links.push(linkInput.value.trim());
                    const tag = document.createElement('span');
                    tag.className = "px-2 py-1 rounded-full bg-green-100 text-green-700 text-[10px]";
                    tag.innerText = "New Link";
                    displayDiv.appendChild(tag);
                    linkInput.value = '';
                }
            };
        }
    }

   saveBtn.onclick = async () => {
    const targetField = (userRole === 'p2') ? 'nilai_target' : 
                        (userRole === 'verifikator') ? 'nilai_verifikator_internal' : 
                        'nilai_asesor_internal';

    const checked = content.querySelector(`.kriteria-cb[data-field="${targetField}"]:checked`);
    
    if (!checked) {
        return Swal.fire({
            title: 'Peringatan',
            text: 'Silakan pilih salah satu Level penilaian terlebih dahulu!',
            icon: 'warning',
            confirmButtonColor: '#3085d6'
        });
    }

    const result = await Swal.fire({
        title: 'Simpan Penilaian?',
        text: "Data akan disimpan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

            const fd = new FormData();
            const payloadKriteria = {
                kriteria_id: null,
                nilai_target: null,
                nilai_asesor_internal: null,
                nilai_verifikator_internal: null
            };

            content.querySelectorAll('.kriteria-cb:checked').forEach(cb => {
                const field = cb.dataset.field;
                payloadKriteria[field] = cb.value;
                if (!payloadKriteria.kriteria_id) payloadKriteria.kriteria_id = cb.dataset.kriteria;
            });

            if (!payloadKriteria.kriteria_id) {
                const first = content.querySelector('.kriteria-cb');
                if (first) payloadKriteria.kriteria_id = first.dataset.kriteria;
            }

            const mapCatatan = {};
            content.querySelectorAll('.catatan-input').forEach(inp => {
                const cid = inp.dataset.id;
                mapCatatan[cid] = {
                    id_catatan: cid,
                    nama_catatankriteria: inp.value,
                    links: buktiStore[cid] ? buktiStore[cid].links : []
                };
            });

            fd.append('tahun', selectTahun.value);
            fd.append('id_indikator', activeIndikatorId);
            fd.append('kriteria', JSON.stringify([payloadKriteria])); 
            fd.append('catatan', JSON.stringify(mapCatatan));
            fd.append('bukti_diklik', buktiClicked ? '1' : '0');
            fd.append('_token', '{{ csrf_token() }}');

            Object.keys(buktiStore).forEach(id => {
                if (buktiStore[id].files) {
                    buktiStore[id].files.forEach(f => fd.append('file_bukti[]', f));
                }
            });

            try {
                const res = await fetch('/penilaian-kriteria/store', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    Swal.fire('Berhasil!', 'Data disimpan.', 'success').then(() => window.location.reload());
                } else {
                    throw new Error('Gagal menyimpan.');
                }
            } catch (e) {
                Swal.fire('Gagal', e.message, 'error');
            }
        }
    };

    document.querySelectorAll('.indikator-item').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
        const id = btn.dataset.id;
        const nomor = btn.dataset.nomor; 
        showModal(id, nomor);
        };
    });

    document.getElementById('close-modal').onclick = () => modal.classList.add('hidden');
});
</script>
</x-app-layout>