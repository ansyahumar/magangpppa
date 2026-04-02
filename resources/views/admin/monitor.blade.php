@extends('admin.layouts.app')
    <link rel="icon" type="image/x-icon"
      href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
    <title>Monitoring</title>
@section('content')
<div class="max-w-full mx-auto px-2 sm:px-6 py-6">
    
    <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-6 animate-fade-in">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                Monitoring & Penilaian Eksternal
            </h2>
            <p class="text-sm text-gray-500">Tahun Aktif: <span class="badge bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold">{{ $tahun }}</span></p>
        </div>

        <form method="get" action="{{ route('admin.monitoring') }}" class="bg-white/80 dark:bg-gray-800/80 p-2 rounded-2xl shadow-sm border border-gray-200">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black uppercase text-gray-400 ml-2 tracking-wider">Filter Tahun</label>
            <select name="tahun" id="global-select-tahun" class="form-select border-none focus:ring-0 text-sm font-bold bg-transparent cursor-pointer text-slate-700 dark:text-white" onchange="this.form.submit()">
                @php
                    $listTahun = \DB::table('domain')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
                @endphp
                @foreach($listTahun as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="h-8 w-[1px] bg-gray-200 dark:bg-gray-700"></div>

        @php
            $totalNilai = \DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->sum('nilai_akhir_external');

            $countIndikator = \DB::table('indikator')
                ->where('tahun', $tahun)
                ->count();

            $nilaiIndeks = $countIndikator > 0 ? ($totalNilai / $countIndikator) : 0;
            
            $persenProgres = round(($nilaiIndeks / 5) * 100);
        @endphp

        <div class="flex items-center gap-3 px-2">
            <div class="flex flex-col items-end">
                <span class="text-xs font-black text-blue-600 dark:text-blue-400 leading-none">{{ $persenProgres }}%</span>
                <span class="text-[9px] uppercase font-bold text-gray-400 tracking-tighter">Progress</span>
            </div>
            <div class="w-12 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden border border-gray-200/50">
                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-1000" 
                     style="width: {{ $persenProgres }}%">
                </div>
            </div>
        </div>
    </div>
</form>
    </div>

    @php 
        $globalCounter = 1; 
    @endphp

    @forelse($domains as $indexDomain => $d)
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-2xl border-l-8 border-blue-600">
                <span class="text-2xl font-black text-blue-600">{{ $indexDomain + 1 }}.</span>
                <h3 class="text-xl font-extrabold uppercase tracking-tight text-gray-800 dark:text-gray-200">
                    Domain: {{ $d->nama_domain }}
                </h3>
            </div>
            
            @foreach($d->aspek as $a)
                <div class="ml-4 sm:ml-8 mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-1 w-6 bg-gray-300 rounded"></div>
                        <h4 class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">
                            Aspek: {{ $a->nama_aspek }}
                        </h4>
                    </div>

                    <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-[10px] uppercase font-black tracking-wider bg-gray-50/80 text-gray-500">
                                        <th class="px-4 py-4 border-r w-12 text-center">No</th>
                                        <th class="px-4 py-4 border-r text-left min-w-[300px]">Indikator</th>
                                        <th class="px-2 py-4 border-r text-center bg-amber-50">Target</th>
                                        <th class="px-2 py-4 border-r text-center bg-blue-50">Asesor In</th>
                                        <th class="px-2 py-4 border-r text-center bg-blue-50">Verif In</th>
                                        <th class="px-2 py-4 border-r text-center bg-emerald-50">Asesor Ex</th>
                                        <th class="px-2 py-4 text-center bg-emerald-100 font-bold">Final</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($a->indikator as $ind)
                                        @php $val = $draft->get($ind->id_indikator); @endphp
                                        <tr class="hover:bg-blue-50/40 transition-all group">
                                            <td class="px-4 py-4 text-center text-sm font-black text-gray-400 border-r bg-gray-50/30 group-hover:text-blue-600">
                                                {{ $globalCounter++ }}
                                            </td>
<td class="px-4 py-4 border-r">
    <button type="button" 
        class="text-left text-sm font-semibold text-gray-800 dark:text-gray-200 indikator-item hover:underline" 
        data-id="{{ $ind->id_indikator }}"
        data-nomor="{{ $globalCounter - 1 }}">
        {{ $ind->nama_indikator }}
    </button>
</td>
                                            
                                            <td class="text-center border-r font-bold text-amber-600 italic">{{ $val->nilai_target ?? '-' }}</td>
                                            <td class="text-center border-r font-bold text-blue-600">{{ $val->nilai_asesor_internal ?? '-' }}</td>
                                            <td class="text-center border-r font-bold text-blue-800">{{ $val->nilai_verifikator_internal ?? '-' }}</td>
                                            <td class="text-center border-r font-bold text-emerald-600">{{ $val->nilai_asesor_external ?? '-' }}</td>
                                            <td class="text-center font-black text-emerald-900 bg-emerald-50/30 text-base">{{ $val->nilai_akhir_external ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
            <p class="text-gray-400 font-bold italic">Tidak ada data Master untuk tahun {{ $tahun }}.</p>
        </div>
    @endforelse
</div>

<div id="modal-kriteria" class="fixed inset-0 hidden z-50 overflow-hidden flex items-center justify-center p-2 sm:p-4 transition-all duration-300">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modal-overlay"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-7xl flex flex-col max-h-[95vh] border border-gray-200 dark:border-gray-700 transform scale-95 transition-transform duration-300" id="modal-container">
        
        <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl text-white shadow-lg shadow-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-white">Sinkronisasi Detail Penilaian</h3>
                    <p id="modal-subtitle" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"></p>
                </div>
            </div>
            <button id="close-modal" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-all text-gray-500 dark:text-gray-400 text-2xl hover:rotate-90">&times;</button>
        </div>

        <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar" id="kriteria-content"></div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-[10px] font-bold text-gray-400 italic text-center sm:text-left">
                <i class="fas fa-info-circle mr-1"></i> Perubahan akan tercatat di log aktivitas sistem.
            </p>
            <button type="button" id="save-kriteria" class="hidden w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/40 transition-all transform active:scale-95 hover:-translate-y-0.5">
                Simpan Penilaian Eksternal
            </button>
        </div>
    </div>
</div>
<div class="flex justify-end mt-4">
    <form id="form-finalisasi-ex" method="POST" action="{{ route('admin.finalisasi_eksternal') }}">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <button type="button" onclick="confirmEx()" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-emerald-500/50 transition-all transform active:scale-95">
            <i class="fa-solid fa-calculator mr-2"></i> Hitung & Finalisasi Nilai Eksternal
        </button>
    </form>
</div>
<script>
function confirmEx() {
    Swal.fire({
        title: 'Kalkulasi Nilai Eksternal?',
        text: "Sistem akan menghitung Indeks Asesor Eksternal dan Nilai Akhir secara bersamaan untuk tahun {{ $tahun }}.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hitung Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Menghitung...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            document.getElementById('form-finalisasi-ex').submit();
        }
    });
}
</script>
<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
    .animate-slide-up { animation: slideUp 0.5s ease-out forwards; opacity: 0; }

    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { 
        background: #cbd5e1; 
        border-radius: 10px; 
        transition: background 0.3s;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3b82f6; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }

    .kriteria-cb {
        @apply cursor-pointer transition-all duration-200 transform;
    }
    .kriteria-cb:hover { transform: scale(1.2); }
    .kriteria-cb:checked { animation: bounce 0.4s ease; }

    @keyframes bounce {
        0%, 100% { transform: scale(1.2); }
        50% { transform: scale(1.4); }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-kriteria');
    const modalContainer = document.getElementById('modal-container');
    const modalOverlay = document.getElementById('modal-overlay');
    const content = document.getElementById('kriteria-content');
    const saveBtn = document.getElementById('save-kriteria');
    const selectTahun = document.getElementById('global-select-tahun');

    let activeIndikatorId = null;

    async function showModal(indikatorId, nomorUrut) {
        activeIndikatorId = indikatorId;
        const tahunAktifVal = selectTahun.value;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalOverlay.classList.replace('opacity-0', 'opacity-100');
            modalContainer.classList.replace('scale-95', 'scale-100');
        }, 10);

        content.innerHTML = `
            <div class="flex flex-col items-center justify-center py-24 space-y-4">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-100 border-t-blue-600"></div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest animate-pulse">Memuat Data Indikator...</p>
            </div>`;
        saveBtn.classList.add('hidden');

        try {
            const res = await fetch(`/indikator/${indikatorId}/detail?tahun=${tahunAktifVal}`);
            const data = await res.json();
            
            const isExternalFilled = data.kriteria.some(k => k.nilai_akhir_external != null && k.nilai_akhir_external != 0);
            if (!isExternalFilled) {
                saveBtn.classList.remove('hidden');
                saveBtn.classList.add('animate-fade-in');
            }

            renderUI(data, isExternalFilled, nomorUrut);
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal memuat data indikator' });
            closeModalFunc();
        }
    }

    function closeModalFunc() {
        modalOverlay.classList.replace('opacity-100', 'opacity-0');
        modalContainer.classList.replace('scale-100', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

function renderUI(data, isExternalFilled, nomorUrut) {
    const content = document.getElementById('kriteria-content');
    const selectTahun = document.getElementById('global-select-tahun');
    
    const detail = data.detail || { nomor_indikator: '-', nama_indikator: '-' };
    const tahunHistori = data.tahun_histori || (selectTahun.value - 1);
    const cat = (data.catatan && data.catatan.length > 0) ? data.catatan[0] : {bukti: '[]', nama_catatankriteria: '', pencapaian: ''};
    const kriteriaList = data.kriteria || [];
    const getGlobalVal = (fieldName) => {
        const found = kriteriaList.find(item => item[fieldName] != null && item[fieldName] != 0);
        return found ? found[fieldName] : null;
    };

    const gHistori  = getGlobalVal('nilai_histori');
    const gTarget   = getGlobalVal('nilai_target');
    const gAsesorIn = getGlobalVal('nilai_asesor_internal');
    const gVerifIn  = getGlobalVal('nilai_verifikator_internal');
    const gAsesorEx = getGlobalVal('nilai_asesor_external');
    const gAkhirEx  = getGlobalVal('nilai_akhir_external');

    let html = `
        <div class="mb-6 p-5 bg-blue-50 border-l-4 border-blue-600 rounded-r-2xl text-left">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Indikator ${nomorUrut}</p>
                    <h4 class="text-lg font-bold text-gray-900">${detail.nama_indikator}</h4>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6 text-left">
            <table class="min-w-full text-sm text-left divide-y divide-gray-200">
                <thead class="bg-gray-50 font-bold text-center uppercase text-[10px]">
                    <tr>
                        <th rowspan="3" class="px-4 py-3 border-r w-12 text-center">Lv</th>
                        <th rowspan="3" class="px-4 py-3 border-r min-w-[300px] text-left">Kriteria Penilaian</th>
                        <th class="px-4 py-2 border-b border-r bg-gray-100">Tahun ${tahunHistori}</th>
                        <th colspan="5" class="px-4 py-2 border-b">Tahun Aktif (${selectTahun.value})</th>
                    </tr>
                    <tr class="text-[10px] uppercase">
                        <th class="border-r">Nilai Akhir</th>
                        <th class="px-4 py-2 border-r">Target</th>
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
        const disAdmin = isExternalFilled ? 'disabled' : '';
        const isCheckedHistori  = (Number(gHistori) === level) ? 'checked' : '';
        const isCheckedTarget   = (Number(gTarget) === level) ? 'checked' : '';
        const isCheckedAsesorIn = (Number(gAsesorIn) === level) ? 'checked' : '';
        const isCheckedVerifIn  = (Number(gVerifIn) === level) ? 'checked' : '';
        const isCheckedAsesorEx = (Number(gAsesorEx) === level) ? 'checked' : '';
        const isCheckedAkhirEx  = (Number(gAkhirEx) === level) ? 'checked' : '';

        html += `
            <tr class="hover:bg-blue-50/30 transition-colors text-left text-xs">
                <td class="text-center font-bold text-gray-500 border-r bg-gray-50/50">${level}</td>
                <td class="p-4 leading-relaxed border-r text-left">${k.nama_kriteria}</td>
                
                <td class="text-center border-r bg-gray-50/30">
                    <input type="checkbox" ${isCheckedHistori} disabled class="rounded w-4 h-4">
                </td>
                <td class="text-center border-r">
                    <input type="checkbox" ${isCheckedTarget} disabled class="rounded w-4 h-4 text-amber-500">
                </td>
                <td class="text-center border-r">
                    <input type="checkbox" ${isCheckedAsesorIn} disabled class="rounded w-4 h-4 text-blue-300">
                </td>
                <td class="text-center border-r">
                    <input type="checkbox" ${isCheckedVerifIn} disabled class="rounded w-4 h-4 text-blue-500">
                </td>
                <td class="text-center border-r bg-emerald-50/20">
                    <input type="checkbox" class="kriteria-cb rounded w-4 h-4 text-emerald-600" 
                        ${isCheckedAsesorEx} ${disAdmin} 
                        data-kriteria="${k.id_kriteria}" data-field="nilai_asesor_external" value="${level}">
                </td>
                <td class="text-center bg-emerald-50/20">
                    <input type="checkbox" class="kriteria-cb rounded w-4 h-4 text-emerald-700" 
                        ${isCheckedAkhirEx} ${disAdmin} 
                        data-kriteria="${k.id_kriteria}" data-field="nilai_akhir_external" value="${level}">
                </td>
            </tr>`;
    });

    html += `</tbody></table></div>`;

    html += `
    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-left mb-6">
        <h4 class="font-bold text-gray-800 mb-6 flex items-center gap-2 underline italic uppercase text-[10px]">
            <i class="fa-solid fa-circle-info text-blue-500"></i> Rekapitulasi Informasi Pendukung (Read-Only)
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-blue-600 uppercase tracking-widest block mb-2">
                        <i class="fa-solid fa-user-pen mr-1"></i> Catatan Asesor Internal
                    </label>
                    <div class="text-xs text-gray-700 bg-white p-4 rounded-xl border border-blue-100 min-h-[80px] shadow-sm italic leading-relaxed">
                        ${cat.nama_catatankriteria || 'Tidak ada catatan asesor.'}
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Lampiran Bukti (File & Link)</label>
                    <div class="flex flex-wrap gap-2">`;
                   try { 
  const buktiArray = JSON.parse(cat.bukti || '[]');

if (buktiArray.length > 0) {
    buktiArray.forEach((b, index) => {

        if (!b) return;

        let value = b.trim();
        let isUrl = false;
        let href = '';

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

        const label = isUrl ? 'LINK BUKTI' : 'FILE BUKTI';
        const icon = isUrl ? 'fa-link' : 'fa-file-pdf';

        html += `
            <a href="${href}" target="_blank" 
               class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 text-[10px] rounded-lg font-bold border border-blue-200 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                 <i class="fa-solid ${icon} mr-2"></i> ${label} ${index + 1}
            </a>`;
    });
    } else { 
        html += '<span class="text-gray-400 text-xs italic">Tidak ada lampiran.</span>'; 
    }
} catch(e) { 
    html += '<span class="text-gray-400 text-xs italic">Gagal memuat bukti.</span>'; 
}
    html += `       
    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-2">
                        <i class="fa-solid fa-user-check mr-1"></i> Analisis Pencapaian (Verifikator)
                    </label>
                    <div class="text-xs text-gray-700 bg-emerald-50/30 p-4 rounded-xl border border-emerald-100 min-h-[80px] shadow-sm leading-relaxed">
                        ${cat.pencapaian || '<span class="text-gray-400 italic">Belum ada analisis dari verifikator.</span>'}
                    </div>
                </div>
                <div>
        <label class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest block mb-2">
            <i class="fa-solid fa-comment-dots mr-1"></i> Catatan Eksternal (Admin)
        </label>
        <textarea id="input-catatan-external" 
            class="w-full text-xs text-gray-700 bg-white p-4 rounded-xl border border-emerald-200 focus:ring-emerald-500 focus:border-emerald-500 min-h-[100px] shadow-sm leading-relaxed"
            placeholder="Tambahkan catatan penilaian eksternal di sini..."
            ${isExternalFilled ? 'disabled' : ''}>${cat.catatan_external || ''}</textarea>
    </div>

                <div class="p-3 bg-gray-100/50 rounded-lg">
                    <p class="text-[9px] text-gray-500 italic">
                        * Data di atas adalah data final yang telah dikunci. Admin hanya memiliki akses baca.
                    </p>
                </div>
            </div>
        </div>
    </div>`;

    html += `
        <div class="mb-6">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left"></i> Log Aktivitas Pengisian (Admin)
            </h4>
            <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm">
                <table class="min-w-full text-[10px] text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Aksi</th>
                            <th class="px-4 py-2 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700 bg-white dark:bg-gray-800">`;

    const tahunAktifDropdown = String(selectTahun.value);
    const filteredLogsAdmin = (data.logs || []).filter(log => {
        if (!log.id_indikator || String(log.id_indikator) !== String(activeIndikatorId)) return false;
        const logTahun = log.tahun ? String(log.tahun) : String(new Date(log.created_at).getFullYear());
        return logTahun === tahunAktifDropdown;
    });

  if (filteredLogsAdmin.length > 0) {
filteredLogsAdmin.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).forEach(log => {
const date = new Date(log.created_at);
const formattedDate =  date.toLocaleString('id-ID', { 
day: '2-digit', 
month: 'short', 
year: 'numeric', 
hour: '2-digit', 
minute: '2-digit' 
});

let displayRole = log.role; 


const roleLower = log.role ? log.role.toLowerCase() : '';
if (roleLower === 'p1') {
displayRole = 'Pemimpin';
} else if (roleLower === 'p2') {
displayRole = 'Karodatin';
} else if (roleLower === 'admin') {
displayRole = 'Administrator';
}

html += `
<tr>
<td class="px-4 py-2 font-bold text-blue-600 dark:text-blue-400">${log.name}</td>
<td class="px-4 py-2 font-semibold text-gray-500 uppercase">${displayRole}</td>
    <td class="px-4 py-2 dark:text-gray-300">${log.aksi}</td>
    <td class="px-4 py-2 text-right text-gray-400 font-mono">${formattedDate}</td>
    </tr>`;
    });
} else {
    html += `<tr><td colspan="4" class="px-4 py-4 text-center text-gray-400 italic">Tidak ada aktivitas di tahun ${tahunAktifDropdown}.</td></tr>`;
}


    html += `</tbody></table></div></div>`;
    content.innerHTML = html;

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
    const checkedAsesorEx = content.querySelector('.kriteria-cb[data-field="nilai_asesor_external"]:checked');
    const checkedAkhirEx = content.querySelector('.kriteria-cb[data-field="nilai_akhir_external"]:checked');

    if (!checkedAsesorEx || !checkedAkhirEx) {
        return Swal.fire({
            title: 'Penilaian Belum Lengkap!',
            text: 'Silakan pilih level untuk Asesor Eksternal DAN Nilai Akhir sebelum menyimpan.',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        });
    }

    const result = await Swal.fire({
        title: 'Simpan Penilaian?',
        text: "Data eksternal akan dikunci setelah disimpan.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Simpan Sekarang',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        Swal.fire({ 
            title: 'Sedang Memproses...', 
            allowOutsideClick: false, 
            didOpen: () => Swal.showLoading() 
        });
        
        const kriteria = {};
        content.querySelectorAll('.kriteria-cb:checked').forEach(cb => {
            const kid = cb.dataset.kriteria;
            if (!kriteria[kid]) kriteria[kid] = { kriteria_id: kid };
            kriteria[kid][cb.dataset.field] = cb.value;
        });

        const catatanExternalVal = document.getElementById('input-catatan-external').value;

        const fd = new FormData();
        fd.append('tahun', selectTahun.value);
        fd.append('id_indikator', activeIndikatorId);
        fd.append('kriteria', JSON.stringify(Object.values(kriteria)));
        fd.append('catatan_external', catatanExternalVal); 
        fd.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('/penilaian-kriteria/store', { 
                method: 'POST', 
                body: fd, 
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            });
            
            const responseData = await res.json(); // Ambil pesan dari controller

            if (res.ok) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Berhasil!', 
                    text: responseData.message, 
                    showConfirmButton: false, 
                    timer: 1500 
                }).then(() => window.location.reload());
            } else { 
                throw new Error(responseData.message || 'Terjadi kesalahan saat menyimpan.'); 
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
    document.getElementById('close-modal').onclick = closeModalFunc;
    window.onclick = (e) => { if (e.target == modal) closeModalFunc(); };
});
</script>
@endsection