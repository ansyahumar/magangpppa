@extends('layouts.verifikator')

@section('content')
<<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Penilaian Verifikator SPBE 
                <span class="text-blue-600">{{ $tahun }}</span>
            </h2>
            <p class="text-sm text-gray-500">Verifikasi nilai yang telah diinput oleh Asesor Internal.</p>
        </div>
    </div>

     @php $globalIndikatorCount = 1; @endphp

    @foreach($domains as $d)
        <div class="mb-8 overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-700 to-slate-800 text-white font-bold italic">
                Domain {{ $loop->iteration }}: {{ $d->nama_domain }}
            </div>
            
            <div class="p-6 space-y-8">
                @foreach($d->aspek as $indexAspek => $a)
                    <div>
                        <h4 class="mb-4 text-md font-bold text-gray-700 dark:text-gray-200 flex items-center">
                            <span class="w-2 h-6 bg-blue-500 rounded-full mr-3"></span>
                            Aspek {{ $indexAspek + 1 }}: {{ $a->nama_aspek }}
                        </h4>
                        
                        <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-xl">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-16">No</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-left">Indikator</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-32">Nilai Verif</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($a->indikator as $ind)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                            <td class="px-4 py-4 text-center text-sm text-gray-400 font-bold">
                                                {{ $globalIndikatorCount++ }}
                                            </td>

                                            <td class="px-4 py-4">
                                                <button type="button" class="text-left font-bold text-blue-600 dark:text-blue-400 hover:underline indikator-item" 
    data-id="{{ $ind->id_indikator }}" 
    data-nomor="{{ $globalIndikatorCount - 1 }}"> 
    {{ $ind->nama_indikator }}
</button>
                                            </td>

                                            <td class="px-4 py-4 text-center">
                                                @php 
                                                    $val = data_get($draft, $ind->id_indikator);
                                                    $nilaiVerif = $val ? $val->nilai_verifikator_internal : 0;
                                                @endphp
                                                
                                                @if($nilaiVerif > 0)
                                                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-black bg-blue-600 text-white mx-auto shadow-sm">
                                                        {{ (int)$nilaiVerif }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter italic">Belum Diverif</span>
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
    </div>

<div id="modal-kriteria" class="fixed inset-0 hidden z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-6xl w-full flex flex-col max-h-[90vh] overflow-hidden border dark:border-gray-700">
        <div class="flex justify-between items-center px-6 py-4 border-b dark:border-gray-700 text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-900/50">
            <div>
                <h3 class="text-xl font-bold italic">Verifikasi Detail Indikator</h3>
                <p id="modal-subtitle" class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-bold">Panel Kontrol Verifikator</p>
            </div>
            <button id="close-modal" class="p-2 bg-gray-200 dark:bg-gray-700 rounded-full dark:text-white font-bold hover:bg-red-500 transition-all">✕</button>
        </div>
        <div class="p-6 overflow-y-auto custom-scrollbar" id="kriteria-content"></div>
        <div class="px-6 py-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-right">
            <button type="button" id="save-kriteria" class="hidden px-8 py-2.5 bg-blue-600 text-white font-black rounded-xl shadow-lg hover:bg-blue-700 transition-all uppercase text-xs tracking-widest">Simpan Data Verifikasi</button>
        </div>
    </div>
</div>

<div class="fixed bottom-8 right-8">
    <button id="btn-toggle-edit" class="flex items-center gap-3 px-6 py-3 bg-emerald-600 text-white rounded-2xl shadow-2xl hover:bg-emerald-700 transition-all font-bold">
        <i id="edit-icon" class="fa-solid fa-pen-to-square"></i>
        <span id="edit-text">Mode Edit Nilai</span>
    </button>
</div>
<form id="form-finalisasi" method="POST" action="{{ route('penilaian.finalisasi_verifikator') }}">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700">
        <i class="fa-solid fa-calculator mr-2"></i> Hitung Indeks Verifikator
    </button>
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-kriteria');
    const content = document.getElementById('kriteria-content');
    const saveBtn = document.getElementById('save-kriteria');
    const tahunAktif = "{{ $tahun }}";
    const userRole = "{{ Auth::user()->role }}";

    let isEditMode = false;
    let activeIndikatorId = null;

    const btnToggleEdit = document.getElementById('btn-toggle-edit');
    if (btnToggleEdit) {
        btnToggleEdit.onclick = () => {
            isEditMode = !isEditMode;
            btnToggleEdit.classList.toggle('bg-emerald-600');
            btnToggleEdit.classList.toggle('bg-red-600');
            document.getElementById('edit-text').innerText = isEditMode ? "Matikan Mode Edit" : "Mode Edit Nilai";
            if (isEditMode) Swal.fire({ icon: 'info', title: 'Mode Edit Aktif', timer: 1000, showConfirmButton: false });
        };
    }

    async function showModal(indikatorId, nomorUrut) {
        activeIndikatorId = indikatorId;
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div></div>';
        saveBtn.classList.add('hidden');

        try {
            const res = await fetch(`/indikator/${indikatorId}/detail?tahun=${tahunAktif}`);
            if (!res.ok) throw new Error('Network response was not ok');

            const data = await res.json();
            const kriteriaList = data.kriteria || [];
            const isVerifFilled = kriteriaList.some(k => k.nilai_verifikator_internal != null && k.nilai_verifikator_internal != 0);

            if (userRole === 'verifikator' && (!isVerifFilled || isEditMode)) {
                saveBtn.classList.remove('hidden');
            }
            renderUI(data, isVerifFilled, nomorUrut);
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Gagal memuat data: ' + e.message, 'error');
        }
    }

    function renderUI(data, isVerifFilled, nomorUrut) {
        const detail = data.detail || { nomor_indikator: '-', nama_indikator: '-', nama_domain: '-', nama_aspek: '-' };
        const kriteriaList = data.kriteria || [];
        const logs = data.logs || [];
        const tahunHistori = data.tahun_histori || (parseInt(tahunAktif) - 1);

        const isVerifRole = (userRole === 'verifikator');
        const canEditVerif = (isVerifRole && (!isVerifFilled || isEditMode));

        const cat = (data.catatan && data.catatan.length > 0)
            ? data.catatan[0]
            : { id_catatan: null, pencapaian: '', bukti: '[]', pencapaian_verif_internal: '', nama_catatankriteria: '' };

        const levelAsesorInt = kriteriaList.find(k => k.nilai_asesor_internal != null)?.nilai_asesor_internal;
        const levelHistori = kriteriaList.find(k => k.nilai_histori != null)?.nilai_histori;
        const levelTarget = kriteriaList.find(k => k.nilai_target != null)?.nilai_target;
        const levelAsesorExt = kriteriaList.find(k => k.nilai_asesor_external != null)?.nilai_asesor_external;
        const levelAkhirExt = kriteriaList.find(k => k.nilai_akhir_external != null)?.nilai_akhir_external;
        const levelVerifAktif = kriteriaList.find(k => k.nilai_verifikator_internal != null)?.nilai_verifikator_internal;
        const currentIdIndikator = activeIndikatorId;

        let html = `
        <div class="mb-6 p-5 bg-blue-50 border-l-4 border-blue-600 rounded-r-2xl text-left">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded uppercase tracking-wider">${detail.nama_domain}</span>
                        <span class="px-2 py-0.5 bg-white text-blue-600 border border-blue-200 text-[10px] font-bold rounded uppercase tracking-wider">${detail.nama_aspek}</span>
                    </div>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Indikator ${nomorUrut}</p>
                    <h4 class="text-lg font-bold text-gray-900">${detail.nama_indikator}</h4>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <a href="/panduan/${currentIdIndikator}/penjelasan" target="_blank" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-blue-50 text-blue-700 rounded-lg border border-blue-200 transition font-bold text-xs shadow-sm">Penjelasan</a>
                    <a href="/panduan/${currentIdIndikator}/penulisan" target="_blank" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200 transition font-bold text-xs shadow-sm">Tata Cara</a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6 shadow-sm">
            <table class="min-w-full text-[11px] text-left divide-y divide-gray-200">
                <thead class="bg-gray-50 font-bold text-center text-gray-600">
                    <tr>
                        <th rowspan="3" class="px-4 py-3 border-r w-12 text-center">Lv</th>
                        <th rowspan="3" class="px-4 py-3 border-r min-w-[300px] text-left">Kriteria Penilaian</th>
                        <th class="px-4 py-2 border-b border-r bg-gray-100/50 italic">Tahun ${tahunHistori}</th>
                        <th colspan="5" class="px-4 py-2 border-b">Tahun Aktif (${tahunAktif})</th>
                    </tr>
                    <tr class="text-[9px] uppercase tracking-tighter">
                        <th class="border-r bg-gray-100/50">Nilai Akhir</th>
                        <th class="px-4 py-2 border-r">Target</th>
                        <th colspan="2" class="px-4 py-2 bg-blue-50 text-blue-700 font-black border-r">Internal</th>
                        <th colspan="2" class="px-4 py-2 bg-amber-50 text-amber-700 font-black">Eksternal</th>
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
                <tbody class="divide-y divide-gray-100 bg-white">`;

        kriteriaList.forEach((k, i) => {
            const level = (i % 5) + 1;
            html += `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="text-center font-bold text-gray-400 border-r bg-gray-50/50">${level}</td>
                    <td class="p-3 leading-relaxed border-r text-[11px]">${k.nama_kriteria}</td>
                    <td class="text-center border-r bg-gray-50/30"><input type="checkbox" ${parseFloat(levelHistori) === level ? 'checked' : ''} disabled class="rounded w-3 h-3"></td>
                    <td class="text-center border-r"><input type="checkbox" ${parseFloat(levelTarget) === level ? 'checked' : ''} disabled class="rounded w-3 h-3 text-emerald-500"></td>
                    <td class="text-center border-r bg-slate-50/50"><input type="checkbox" ${parseFloat(levelAsesorInt) === level ? 'checked' : ''} disabled class="rounded w-3 h-3 text-slate-500"></td>
                    <td class="text-center border-r bg-blue-50">
                        <input type="checkbox" class="kriteria-cb w-4 h-4 rounded text-blue-600 cursor-pointer" ${parseFloat(levelVerifAktif) === level ? 'checked' : ''} ${canEditVerif ? '' : 'disabled'} data-kriteria="${k.id_kriteria}" value="${level}">
                    </td>
                    <td class="text-center border-r bg-amber-50/30"><input type="checkbox" ${parseFloat(levelAsesorExt) === level ? 'checked' : ''} disabled class="rounded w-3 h-3 text-amber-500"></td>
                    <td class="text-center bg-amber-50/30"><input type="checkbox" ${parseFloat(levelAkhirExt) === level ? 'checked' : ''} disabled class="rounded w-3 h-3 text-amber-600"></td>
                </tr>`;
        });

        html += `</tbody></table></div>`;

        html += `
        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 text-left">
            <h4 class="font-bold text-gray-800 mb-4">Catatan & Bukti Pendukung</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Catatan Asesor Internal</label>
                    <div class="p-3 bg-white border border-gray-200 rounded-lg text-xs text-gray-600 min-h-[100px]">${cat.nama_catatankriteria || '-'}</div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Catatan Verifikator</label>
                    <textarea id="pencapaian-verif" class="w-full border-gray-200 rounded-lg text-xs" rows="4" ${canEditVerif ? '' : 'readonly'} placeholder="Masukkan catatan verifikasi...">${cat.pencapaian_verif_internal || ''}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Lampiran Bukti (Asesor)</label>
                    <div class="flex flex-wrap gap-2">`;

        let buktiData = [];
        try {
            buktiData = typeof cat.bukti === 'string' ? JSON.parse(cat.bukti) : (cat.bukti || []);
        } catch (e) { buktiData = []; }

        if (buktiData.length > 0) {
            buktiData.forEach((file, index) => {
                const rawPath = typeof file === 'string' ? file : (file.path || '');
                if (!rawPath) return;

                let finalUrl = '';
                let isUrl = false;
                try {
                    const urlObj = new URL(rawPath.startsWith('www') ? `https://${rawPath}` : rawPath);
                    finalUrl = urlObj.href;
                    isUrl = true;
                } catch (err) {
                    const filename = encodeURIComponent(rawPath.split('/').pop());
                    finalUrl = `/view-bukti/${filename}`;
                    isUrl = false;
                }

                const icon = isUrl ? `<i class="fa-solid fa-link text-emerald-500"></i>` : `<i class="fa-solid fa-file-pdf text-blue-500"></i>`;
                const theme = isUrl ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-blue-50 border-blue-200 text-blue-700';
                const label = isUrl ? `Link ${index + 1}` : `File ${index + 1}`;

                html += `
                <a href="${finalUrl}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-2.5 py-1.5 rounded-full border ${theme} text-[10px] font-bold transition-all shadow-sm hover:shadow-md">
                    ${icon} <span class="ml-1">${label}</span>
                </a>`;
            });
        } else {
            html += `<p class="text-[10px] text-gray-400 italic">Tidak ada lampiran bukti.</p>`;
        }

        html += `</div></div></div></div>`;

        html += `
        <div class="border-t pt-6 mt-6 text-left">
            <h5 class="text-xs font-black text-blue-600 uppercase tracking-[0.2em] mb-4 flex items-center">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Status Pengisian Indikator
            </h5>
            <div class="space-y-3 pr-2">`;

        if (Array.isArray(logs) && logs.length > 0) {
            logs.forEach(log => {
                const roleName = (log.role || 'unknown').toLowerCase();
                if (roleName !== 'user' && roleName !== 'verifikator') return;

                const isVerif = roleName === 'verifikator';
                const isUpdate = log.aksi.includes('Perubahan') || log.aksi.includes('Update');

                html += `
                <div class="flex justify-between items-start ${isVerif ? 'bg-blue-50/50' : 'bg-orange-50/50'} p-3 rounded-xl border ${isVerif ? (isUpdate ? 'border-blue-400 border-dashed' : 'border-blue-100') : 'border-orange-100'} shadow-sm">
                    <div class="flex gap-3">
                        <div class="mt-1 w-8 h-8 rounded-full flex items-center justify-center ${isVerif ? (isUpdate ? 'bg-blue-800' : 'bg-blue-600') : 'bg-orange-500'} text-white text-xs shadow-md">
                            <i class="fa-solid ${isVerif ? (isUpdate ? 'fa-user-pen' : 'fa-user-check') : 'fa-user-pen'}"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-[11px] font-black text-gray-800">${log.name || 'User'}</p>
                                <span class="text-[8px] px-1.5 py-0.5 rounded font-bold uppercase bg-blue-600 text-white">
                                    ${isVerif ? 'VERIFIKATOR' : 'ASESOR INTERNAL'}
                                </span>
                            </div>
                            <p class="text-[10px] text-gray-600 italic">${log.aksi}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] text-gray-400 font-medium block">${log.created_at || '-'}</span>
                    </div>
                </div>`;
            });
        } else {
            html += `<p class="text-[10px] text-gray-400 italic px-4">Belum ada riwayat aktivitas.</p>`;
        }

        html += `</div></div>`;
        content.innerHTML = html;

        const checkboxes = content.querySelectorAll('.kriteria-cb');
        checkboxes.forEach(cb => {
            cb.onchange = function() {
                if (this.checked) {
                    checkboxes.forEach(other => { if (other !== this) other.checked = false; });
                }
            };
        });
    }

    saveBtn.onclick = async () => {
        const checked = content.querySelector('.kriteria-cb:checked');
        const pencapaianVerif = document.getElementById('pencapaian-verif')?.value || '';

        if (!checked) {
            Swal.fire('Peringatan', 'Silakan pilih salah satu kriteria verifikasi sebelum menyimpan.', 'warning');
            return;
        }

        const confirmTitle = isEditMode ? 'Update Data (Mode Edit)?' : 'Simpan Data Verifikasi?';
        const confirmText = isEditMode 
            ? 'Anda akan merubah nilai verifikasi yang sudah ada pada indikator ini.' 
            : 'Nilai verifikasi baru akan disimpan ke dalam sistem.';

        const result = await Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan'
        });

        if (result.isConfirmed) {
            Swal.fire({ title: 'Sedang Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const fd = new FormData();
            const kriteria = [{
                kriteria_id: checked.dataset.kriteria,
                nilai_verifikator_internal: checked.value
            }];

            fd.append('tahun', tahunAktif);
            fd.append('id_indikator', activeIndikatorId);
            fd.append('kriteria', JSON.stringify(kriteria));
            fd.append('pencapaian', pencapaianVerif);
            fd.append('is_edit_mode', isEditMode ? '1' : '0');
            fd.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('/penilaian-kriteria/store', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const responseData = await res.json();
                if (res.ok) {
                    Swal.fire('Berhasil', isEditMode ? 'Perubahan berhasil disimpan.' : 'Data berhasil diinput.', 'success')
                        .then(() => window.location.reload());
                } else {
                    throw new Error(responseData.message || 'Gagal menyimpan data.');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error Sistem', 'Gagal memproses permintaan: ' + e.message, 'error');
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

    document.getElementById('close-modal').onclick = () => {
        modal.classList.add('hidden');
    };
});

const formFinalisasi = document.getElementById('form-finalisasi');

if (formFinalisasi) {
    formFinalisasi.onsubmit = async (e) => {
        e.preventDefault(); 
        const allRows = document.querySelectorAll('tbody tr');
        let firstEmptyIndikator = null;
        let countEmpty = 0;

        allRows.forEach(row => {
            const statusBadge = row.querySelector('td:last-child span');
            if (statusBadge && statusBadge.textContent.includes('Belum Diverif')) {
                countEmpty++;
                if (!firstEmptyIndikator) {
                    firstEmptyIndikator = row; 
                }
            }
        });

       if (countEmpty > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: `Masih ada ${countEmpty} indikator yang belum diverifikasi. Silakan lengkapi semua nilai terlebih dahulu.`,
                confirmButtonText: 'Cari Indikator',
                confirmButtonColor: '#f59e0b'
            }).then(() => {
                if (firstEmptyIndikator) {
                    firstEmptyIndikator.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    firstEmptyIndikator.classList.add('bg-amber-100', 'dark:bg-amber-900/40');
                    setTimeout(() => {
                        firstEmptyIndikator.classList.remove('bg-amber-100', 'dark:bg-amber-900/40');
                    }, 3000);
                }
            });
            return false;
        }

       
        const confirmFinal = await Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data yang sudah dihitung indeksnya akan dianggap final untuk tahap ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hitung Sekarang!'
        });

        if (confirmFinal.isConfirmed) {
            formFinalisasi.submit();
        }
    };
}
</script>
@endsection