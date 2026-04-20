<x-app-layout>
    <script>
        document.title = "Form Penilaian SPBE";
    </script>

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Form Penilaian SPBE
        </h2>
    </x-slot>
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
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Form Penilaian SPBE
                    @if(Auth::user()->role === 'p2') <span class="text-amber-600">(Target)</span> @endif
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
    $userAllowedIds = explode(',', Auth::user()->no_id ?? '');
    $userAllowedIds = array_map('trim', $userAllowedIds);
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
                                    @php 
                                        $currentNum = $globalIndikatorCount++; 
                                        $hasAccess = (Auth::user()->role !== 'user' || in_array((string)$currentNum, $userAllowedIds));
                                    @endphp
                                    
                                    <tr id="row-{{ $ind->id_indikator }}" 
                                        class="baris-indikator hover:bg-gray-50 transition-colors border-l-4 border-transparent {{ !$hasAccess ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                                        <td class="px-4 py-4 text-center text-sm text-gray-400 font-bold">
                                            {{ $currentNum }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($hasAccess)
                                                <button type="button" 
                                                    class="text-left font-medium text-blue-600 hover:underline indikator-item" 
                                                    data-id="{{ $ind->id_indikator }}" 
                                                    data-nomor="{{ $currentNum }}"> 
                                                    {{ $ind->nama_indikator }}
                                                </button>
                                            @else
                                                <div class="flex items-center gap-2 text-gray-400 cursor-not-allowed">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                    <span class="text-sm italic">{{ $ind->nama_indikator }}</span>
                                                </div>
                                            @endif
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
        const tahun = document.getElementById('global-select-tahun').value;
        const allRows = document.querySelectorAll('.baris-indikator');
        
        let emptyCount = 0;
        let firstEmptyRow = null;

        allRows.forEach(row => {
            const hasAccess = !row.classList.contains('pointer-events-none');
            
            if (hasAccess) {
                const nilai = parseInt(row.querySelector('.nilai-angka').innerText.trim()) || 0;
                if (nilai === 0) {
                    emptyCount++;
                    if (!firstEmptyRow) firstEmptyRow = row;
                    row.classList.add('ring-2', 'ring-red-500');
                }
            }
        });

        if (emptyCount > 0) {
            Swal.fire('Belum Lengkap', `Ada ${emptyCount} indikator jatah Anda yang belum diisi.`, 'warning');
            firstEmptyRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const konfirmasi = await Swal.fire({
            title: 'Finalisasi Unit Kerja?',
            text: "Indikator jatah Anda akan dikunci. Data akan dikirim ke Verifikator jika semua unit kerja sudah mengisi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Finalisasi'
        });

        if (konfirmasi.isConfirmed) {
            Swal.showLoading();
            const res = await fetch("{{ route('penilaian.process') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ tahun: tahun })
            });
            
            const result = await res.json();
            if (res.ok) {
                Swal.fire('Berhasil', result.message, 'success').then(() => window.location.reload());
            } else {
                throw new Error(result.message);
            }
        }
    } catch (error) {
        Swal.fire('Gagal', error.message, 'error');
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
    let activeNomorUrut = null;
    let buktiClicked = false;

async function showModal(indikatorId, nomorUrut) {
    activeIndikatorId = indikatorId;
    activeNomorUrut = nomorUrut;
    
    const selectTahun = document.getElementById('global-select-tahun');
    const tahunAktifVal = selectTahun ? selectTahun.value : new Date().getFullYear();

    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
    saveBtn.classList.add('hidden');
    
    try {
        const res = await fetch(`/indikator/${indikatorId}/detail?tahun=${tahunAktifVal}`);
        if (!res.ok) throw new Error(`Server error: ${res.status}`);
        const data = await res.json();

        if (!data.kriteria || data.kriteria.length === 0) {
            content.innerHTML = '<div class="p-8 text-center text-gray-500 font-semibold">Data kriteria tidak ditemukan.</div>';
            return;
        }

        const historiRow = data.kriteria.find(k => k.nilai_histori > 0);
        const nilaiHistoriTahunLalu = historiRow ? parseFloat(historiRow.nilai_histori) : 0;
        const isYearFinalizedGlobal = {{ $currentYearIsFinal ? 'true' : 'false' }};
        const isThisIndikatorLocked = data.kriteria.some(k => k.status_vrifU === 'final');
        const finalLockStatus = isYearFinalizedGlobal || isThisIndikatorLocked;
        const isModeHistori = data.mode === 'histori';

        if (!finalLockStatus) {
            if (userRole === 'p2' || (userRole === 'verifikator' && isModeHistori) || (userRole === 'user' && !isModeHistori)) {
                saveBtn.classList.remove('hidden');
            }
        }

        renderUI(data, isModeHistori, nomorUrut, finalLockStatus, nilaiHistoriTahunLalu);

    } catch (e) {
        console.error("Gagal memuat modal:", e);
        Swal.fire('Error', e.message, 'error');
        modal.classList.add('hidden');
    }
}

    function renderUI(data, isModeHistori, nomorUrut, isYearFinalized, nilaiHistori) {
        const detail = data.detail || { nomor_indikator: '-', nama_indikator: '-' };
        const tahunHistoriVal = data.tahun_histori || (parseInt(tahunAktif) - 1);
        const cat = (data.catatan && data.catatan.length > 0) ? data.catatan[0] : {id_catatan: 'new', nama_catatankriteria: '', pencapaian: '', bukti: '[]'};
        const nilaiHistoriIndikator = data.kriteria.find(k => parseFloat(k.nilai_histori) > 0)?.nilai_histori;
        const isVerifRole = (userRole === 'verifikator');
        const isUserRole = (userRole === 'user');
        const lockNotes = isYearFinalized ? 'readonly' : '';

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
                            <th class="px-2 py-2 border-r"></th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th class="px-2 py-2 border-r">Verif</th>
                            <th class="px-2 py-2 border-r">Asesor</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">`;

        data.kriteria.forEach((k, i) => {
            const level = (i % 5) + 1;
            const isVerified = (k.status_target === 'verified');
            const isP2OrKord = (userRole === 'p2' || userRole === 'koordinator' || userRole === 'admin');

            const chkTarget = (Number(k.nilai_target) === level && (isVerified || isP2OrKord)) ? 'checked' : '';
            const chkAsesor = (Number(k.nilai_asesor_internal) === level) ? 'checked' : '';
            const chkVerif  = (Number(k.nilai_verifikator_internal) === level) ? 'checked' : '';
          const chkHistori = (nilaiHistori > 0 && Math.round(nilaiHistori) === level) ? 'checked' : '';
            const chkAsesorExt = (Number(k.nilai_asesor_external) === level) ? 'checked' : '';
            const chkAkhirExt = (Number(k.nilai_akhir_external) === level) ? 'checked' : '';
            
            const disTarget = 'disabled'; 
            const disAsesor = (isYearFinalized) ? 'disabled' : (isUserRole && !isModeHistori ? '' : 'disabled');
            const disVerif  = (isYearFinalized) ? 'disabled' : (isVerifRole && isModeHistori ? '' : 'disabled');

            html += `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="text-center font-bold text-gray-500 border-r bg-gray-50/50">${level}</td>
                    <td class="p-4 text-xs leading-relaxed border-r">${k.nama_kriteria}</td>
                    <td class="text-center border-r bg-gray-50/30">
                        <input type="checkbox" ${chkHistori} disabled class="rounded w-4 h-4">
                    </td>
                    <td class="text-center border-r ${isVerified ? 'bg-green-50/50' : 'bg-amber-50/30'}">
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

        html += `</tbody></table></div>
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-left">
                <h4 class="font-bold text-gray-800 mb-4">Catatan & Bukti Pendukung</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase flex justify-between">
                            <span>Catatan</span>
                            <span class="text-red-500 text-[9px]">* Wajib diisi</span>
                        </label>
                        <textarea class="catatan-input w-full border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-lg text-xs" 
                            placeholder="Berikan penjelasan pencapaian..."
                            rows="4" data-id="${cat.id_catatan}" ${lockNotes}>${cat.nama_catatankriteria ?? ''}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase"> 
                            <span>Lampiran Bukti</span>
                            <span class="text-red-500 text-[9px]">* Minimal 1 file/link</span>
                        </label>
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
                let isUrl = value.startsWith('http') || value.startsWith('www') || (value.includes('.') && !value.toLowerCase().endsWith('.pdf'));
                let finalUrl = isUrl ? (value.startsWith('http') ? value : 'https://' + value) : `/view-bukti/${value.split('/').pop()}`;
                
                const theme = isUrl ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200';
                const icon = isUrl ? 'fa-link' : 'fa-file-pdf';
                html += `
                    <a href="${finalUrl}" target="_blank" class="inline-flex items-center px-2 py-1 rounded-md border ${theme} text-[9px] font-bold shadow-sm hover:opacity-80">
                        <i class="fa-solid ${icon} mr-1"></i> ${isUrl ? 'Link' : 'File'} ${index + 1}
                    </a>`;
            });
            if (buktiArray.length === 0) html += '<span class="text-[9px] text-gray-400 italic">Belum ada bukti lampiran.</span>';
        } catch (e) {
            html += '<span class="text-red-500 text-[9px]">Gagal memuat bukti.</span>';
        }

        html += `</div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Catatan Verifikator</label>
                        <textarea class="pencapaian-input w-full border-gray-200 rounded-lg text-xs bg-gray-100" rows="4" disabled>${cat.pencapaian ?? ''}</textarea>
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
            return Swal.fire('Peringatan', 'Silakan pilih salah satu Level penilaian terlebih dahulu!', 'warning');
        }

        const catatanInput = content.querySelector('.catatan-input');
        const catatanValue = catatanInput ? catatanInput.value.trim() : '';
        const catId = catatanInput ? catatanInput.dataset.id : null;

        if (catatanValue === '') {
            return Swal.fire('Catatan Wajib Diisi', 'Mohon berikan penjelasan terkait indikator ini.', 'warning');
        }

        const hasNewFiles = buktiStore[catId] && buktiStore[catId].files.length > 0;
        const hasNewLinks = buktiStore[catId] && buktiStore[catId].links.length > 0;
        const displayDiv = document.getElementById(`display-${catId}`);
        const hasExistingBukti = displayDiv && (displayDiv.querySelectorAll('a').length > 0);

        if (!hasNewFiles && !hasNewLinks && !hasExistingBukti) {
            return Swal.fire('Bukti Wajib Ada', 'Silakan unggah file atau masukkan link bukti pendukung.', 'warning');
        }

        const result = await Swal.fire({
            title: 'Simpan Penilaian?',
            text: "Data akan disimpan!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!'
        });

        if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

            const fd = new FormData();
            const payloadKriteria = {
                kriteria_id: checked.dataset.kriteria,
                [targetField]: checked.value
            };

            const mapCatatan = {
                [catId]: {
                    id_catatan: catId,
                    nama_catatankriteria: catatanValue,
                    links: buktiStore[catId] ? buktiStore[catId].links : []
                }
            };

            fd.append('tahun', selectTahun.value);
            fd.append('id_indikator', activeIndikatorId);
            fd.append('kriteria', JSON.stringify([payloadKriteria])); 
            fd.append('catatan', JSON.stringify(mapCatatan));
            fd.append('bukti_diklik', buktiClicked ? '1' : '0');
            fd.append('_token', '{{ csrf_token() }}');

            if (buktiStore[catId] && buktiStore[catId].files) {
                buktiStore[catId].files.forEach(f => fd.append('file_bukti[]', f));
            }

            try {
                const res = await fetch('/penilaian-kriteria/store', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    Swal.fire('Berhasil!', 'Data disimpan.', 'success').then(() => window.location.reload());
                } else {
                    throw new Error('Gagal menyimpan ke server.');
                }
            } catch (e) {
                Swal.fire('Gagal', e.message, 'error');
            }
        }
    };

    document.querySelectorAll('.indikator-item').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            showModal(btn.dataset.id, btn.dataset.nomor);
        };
    });

    document.getElementById('close-modal').onclick = () => modal.classList.add('hidden');
});
</script>
</x-app-layout>