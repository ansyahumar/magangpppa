@extends('layouts.kordinator')

@section('title', 'Verifikasi Target Nilai')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 border-b pb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Verifikasi Target Nilai SPBE 
                <span class="text-indigo-600">(Koordinator)</span>
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tinjau usulan target untuk tahun <span class="font-bold text-indigo-600">{{ $tahun }}</span> sebelum dipublikasikan.</p>
        </div>
        
        <div class="flex items-center gap-4">
        </div>
    </div>

    @php 
        $globalIndikatorCount = 1; 
        $globalAspekCount = 1;
    @endphp

    @foreach($domains as $d)
        <div class="mb-10 overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-md font-bold uppercase tracking-wider">
                Domain {{ $loop->iteration }}: {{ $d->nama_domain }}
            </div>
            
            <div class="p-6 space-y-10">
                @foreach($d->aspek as $a)
                    <div>
                        <h4 class="mb-4 text-md font-bold text-gray-700 dark:text-gray-200 flex items-center">
                            <span class="w-2 h-6 bg-indigo-500 rounded-full mr-3"></span>
                            Aspek {{ $globalAspekCount++ }}: {{ $a->nama_aspek }} 
                        </h4>
                        
                        <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-xl">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-16">No</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-left">Indikator Penilaian</th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-center w-40">Target Nilai (Usulan)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($a->indikator as $ind)
                                        <tr class="hover:bg-indigo-50/30 transition-colors">
                                            <td class="px-4 py-4 text-center text-sm text-gray-400 font-bold">
                                                {{ $globalIndikatorCount }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <button type="button" class="text-left font-medium text-indigo-600 indikator-item hover:underline" 
                                                    data-id="{{ $ind->id_indikator }}" 
                                                    data-nomor="{{ $globalIndikatorCount++ }}"> 
                                                    {{ $ind->nama_indikator }}
                                                </button>
                                            </td>
                                           <td class="px-4 py-4 text-center">
    @php 
        $idInd = $ind->id_indikator;
        $nilai = isset($draft[$idInd]) ? (float)$draft[$idInd] : 0;
    @endphp

    @if($nilai > 0)
        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-100 text-indigo-700 font-black border border-indigo-200">
            Level {{ number_format($nilai, 0) }}
        </span>
    @else
        <span class="text-xs text-gray-400 italic">Belum Ada Usulan</span>
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
            $isVerified = DB::table('penilaian_kriteria')
                        ->where('tahun', $tahun)
                        ->where('status_target', 'verified')
                        ->exists();
        @endphp

        @if(!$isVerified)
            <button type="button" onclick="confirmAction('reject')" 
                    class="px-8 py-3 font-bold rounded-2xl text-red-600 border-2 border-red-600 hover:bg-red-50 transition-all active:scale-95 shadow-sm">
                Kembalikan target
            </button>

            <button type="button" onclick="confirmAction('verify')"
                    class="inline-flex items-center px-10 py-3 font-bold rounded-2xl shadow-xl transition-all bg-indigo-600 hover:bg-indigo-700 hover:-translate-y-1 active:scale-95 text-white">
                <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Setujui
            </button>
        @else
            <div class="flex items-center gap-4 px-8 py-4 bg-emerald-50 text-emerald-700 rounded-3xl border border-emerald-200 shadow-sm">
                <div class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-check text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-black uppercase leading-none mb-1">Target Tahun {{ $tahun }} Terverifikasi</p>
                    <p class="text-xs opacity-75 leading-none">Data sudah dipublikasikan ke seluruh form penilaian.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<div id="modal-kriteria" class="fixed inset-0 hidden z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl max-w-5xl w-full flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center px-8 py-5 border-b dark:border-gray-800">
            <div>
                <h3 class="text-xl font-black text-gray-800 dark:text-white">Review Detail Kriteria</h3>
                <p id="modal-subtitle" class="text-xs text-indigo-500 font-bold uppercase tracking-widest mt-1"></p>
            </div>
            <button id="close-modal" class="w-10 h-10 bg-gray-100 hover:bg-red-100 hover:text-red-600 rounded-full transition-colors flex items-center justify-center font-bold">✕</button>
        </div>
        <div class="p-8 overflow-y-auto" id="kriteria-content">
        </div>
        <div class="px-8 py-5 bg-gray-50 dark:bg-gray-800/50 border-t dark:border-gray-800 text-right">
            <button type="button" onclick="document.getElementById('modal-kriteria').classList.add('hidden')" class="px-6 py-2.5 bg-gray-800 text-white font-bold rounded-xl shadow-lg">Tutup Detail</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-kriteria');
    const content = document.getElementById('kriteria-content');
    const tahunAktif = "{{ $tahun }}";

    async function showModal(id, nomor) {
        modal.classList.remove('hidden');
        content.innerHTML = `
            <div class="text-center py-20">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full"></div>
                <p class="mt-4 text-gray-500">Mengambil data usulan terget...</p>
            </div>`;
        
        try {
            const res = await fetch(`/kordinator/get-detail-review/${id}/${tahunAktif}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                renderReviewUI(data, nomor);
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal memuat data detail.', 'error');
            modal.classList.add('hidden');
        }
    }

    function renderReviewUI(data, nomor) {
        const kriteriaList = data.kriteria || [];
        const targetValueDb = data.target_value ? Math.round(data.target_value) : 0;
        const detail = data.detail || {};

        let html = `
            <div class="mb-8 p-6 bg-indigo-50 border-l-4 border-indigo-600 rounded-2xl">
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Indikator ${nomor}</p>
                <h4 class="text-lg font-extrabold text-gray-900">${detail.nama_indikator || 'Nama Indikator'}</h4>
            </div>
            
            <div class="overflow-hidden border border-gray-200 rounded-2xl shadow-sm">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-gray-500 text-[10px] uppercase font-black">
                            <th class="px-6 py-4 text-center w-20">Level</th>
                            <th class="px-6 py-4">Kriteria Penilaian</th>
                            <th class="px-6 py-4 text-center bg-indigo-50 text-indigo-700 w-40">Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">`;

        kriteriaList.forEach((k, i) => {
            const level = i + 1;
            const isChecked = (targetValueDb === level) ? 'checked' : '';

            html += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-5 font-black text-gray-400 text-center">${level}</td>
                    <td class="px-6 py-5 text-gray-600 leading-relaxed">${k.nama_kriteria}</td>
                    <td class="px-6 py-5 text-center bg-indigo-50/30">
                        <input type="checkbox" ${isChecked} disabled 
                               class="w-6 h-6 rounded-lg text-indigo-600 border-indigo-300 shadow-sm focus:ring-0 cursor-not-allowed">
                    </td>
                </tr>`;
        });

        html += `</tbody></table></div>`;
        content.innerHTML = html;
    }

    document.querySelectorAll('.indikator-item').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            showModal(btn.dataset.id, btn.dataset.nomor);
        };
    });

    document.getElementById('close-modal').onclick = () => modal.classList.add('hidden');
});

async function confirmAction(action) {
    const title = action === 'verify' ? 'Setujui & Publish?' : 'Kembalikan ke pengisian target ?';
    const text = action === 'verify' 
        ? "Target akan dipublikasikan secara nasional." 
        : "Data akan dikembalikan dan target dapat edit.";
    
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'verify' ? '#059669' : '#d33',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

        try {
            const response = await fetch("{{ route('koordinator.target.approve') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    tahun: "{{ $tahun }}",
                    action: action
                })
            });

            const data = await response.json();

            if (response.ok) {
                await Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success'
                });

                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                throw new Error(data.message || 'Terjadi kesalahan pada server.');
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }
}
</script>
@endsection