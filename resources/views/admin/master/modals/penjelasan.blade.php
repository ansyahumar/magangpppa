
<style>

    .content-preview table, .ck-content table {
        width: 100% !important; border-collapse: collapse !important;
        margin: 1rem 0 !important; border: 1px solid #d1d5db !important;
    }
    .content-preview table td, .content-preview table th,
    .ck-content table td, .ck-content table th {
        border: 1px solid #d1d5db !important; padding: 10px !important;
    }
    .ck-editor__editable_inline { 
        min-height: 250px; color: #1f2937 !important; 
        background-color: white !important; border-radius: 0 0 12px 12px !important;
    }
    .ck-body-wrapper { z-index: 9999 !important; }
    [x-cloak] { display: none !important; }

    .modal-scroll::-webkit-scrollbar { width: 5px; }
    .modal-scroll::-webkit-scrollbar-track { background: transparent; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .btn-rotate-target {
        transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .btn-rotate-trigger:hover .btn-rotate-target {
        transform: rotate(90deg);
    }
</style>

<div
    x-show="openModal === 'penjelasan'"
    x-cloak
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @open-penjelasan-modal.window="
        mode = 'view';
        if (activeIndikator) {
            formData.penjelasan_kriteria = activeIndikator.penjelasan?.penjelasan_kriteria || '';
            formData.tatacara_penilaian = activeIndikator.penjelasan?.tatacara_penilaian || '';
        }
    "
    @keydown.escape.window="openModal = null"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-[2px] p-4"
>
    <div @click.away="openModal = null"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-98 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="bg-white dark:bg-gray-800 w-full max-w-4xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col overflow-hidden"
    >
        
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 flex justify-between items-center">
            <div class="text-left">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail Penjelasan</h3>
                <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black uppercase tracking-widest mt-0.5">
                    Indikator: <span x-text="activeIndikator?.nomor || '-'"></span>
                </p>
            </div>
            
            <button @click="openModal = null" 
                    class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors active:scale-75">
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-rotate-target h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 bg-white dark:bg-gray-800 modal-scroll">
            
            <div x-show="mode === 'view'" 
                 x-transition:enter="transition duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="p-6 space-y-6">
                
                <div class="space-y-4 text-left">
                    <div class="p-5 bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 rounded-xl">
                        <span class="inline-flex items-center gap-2 text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase mb-3 tracking-widest">
                            <i class="fa-solid fa-circle-info"></i> Penjelasan Kriteria
                        </span>
                        <div class="text-sm text-gray-700 dark:text-gray-300 content-preview">
                            <div x-html="formData.penjelasan_kriteria || '<span class=\'text-gray-400 italic\'>Belum ada data.</span>'"></div>
                        </div>
                    </div>

                    <div class="p-5 bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/50 rounded-xl">
                        <span class="inline-flex items-center gap-2 text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase mb-3 tracking-widest">
                            <i class="fa-solid fa-book"></i> Tata Cara Penulisan
                        </span>
                        <div class="text-sm text-gray-700 dark:text-gray-300 content-preview">
                            <div x-html="formData.tatacara_penilaian || '<span class=\'text-gray-400 italic\'>Belum ada data.</span>'"></div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-6 border-t dark:border-gray-700">
                    <button @click="mode = 'edit'; $nextTick(() => initPenjelasanEditors())" 
                            class="flex-1 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Referensi
                    </button>
                    
                    <template x-if="activeIndikator?.penjelasan">
                        <form :action="'/penjelasan/' + activeIndikator.penjelasan.id_penjelasan_penulisan + '/delete'" method="POST" onsubmit="return confirm('Hapus referensi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-6 py-3 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl font-bold transition-all flex items-center gap-2">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </form>
                    </template>
                </div>
            </div>

<div x-show="mode === 'edit'" x-transition x-cloak class="p-6">
    <form id="formPenjelasanAdmin" method="POST" :action="'/penjelasan/' + activeIndikator?.id + '/update'">
        @csrf 
        @method('PUT')

        <input type="hidden" name="id_indikator" :value="activeIndikator?.id">
        <input type="hidden" name="tahun" value="{{ date('Y') }}">

        <div class="space-y-6 text-left">
            <div>
                <label class="block text-xs font-bold uppercase mb-2">Penjelasan Kriteria</label>
                <div class="rounded-xl overflow-hidden border">
                    <textarea id="editorPenjelasan" name="penjelasan_kriteria"></textarea>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-2">Tata Cara Penilaian</label>
                <div class="rounded-xl overflow-hidden border">
                    <textarea id="editorTatacara" name="tatacara_penilaian"></textarea>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="mode = 'view'" class="px-4 py-2 text-gray-500">Batal</button>
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg active:scale-95 transition-all">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Data
            </button>
        </div>
    </form>
</div>
        </div>
    </div>
</div>

<script>
    function initPenjelasanEditors() {
        const config = { 
            toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'insertTable', 'undo', 'redo'],
            table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'] }
        };
        
        const pEl = document.querySelector('#editorPenjelasan');
        const tEl = document.querySelector('#editorTatacara');

        if (!window.editorPenjelasanInst) {
            ClassicEditor.create(pEl, config).then(ed => { 
                window.editorPenjelasanInst = ed; 
                ed.setData(pEl.value || ''); 
            });
        } else {
            window.editorPenjelasanInst.setData(pEl.value || '');
        }

        if (!window.editorTatacaraInst) {
            ClassicEditor.create(tEl, config).then(ed => { 
                window.editorTatacaraInst = ed; 
                ed.setData(tEl.value || ''); 
            });
        } else {
            window.editorTatacaraInst.setData(tEl.value || '');
        }
    }

document.addEventListener('submit', function (e) {
    if (e.target.id === 'formPenjelasanAdmin') {
        const dataP = window.editorPenjelasanInst ? window.editorPenjelasanInst.getData() : '';
        const dataT = window.editorTatacaraInst ? window.editorTatacaraInst.getData() : '';

        if (!dataP.trim() || !dataT.trim()) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Semua kolom wajib diisi!' });
            return;
        }

        document.querySelector('#editorPenjelasan').value = dataP;
        document.querySelector('#editorTatacara').value = dataT;
        Swal.fire({ 
            title: 'Sedang menyimpan...', 
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading() 
        });
    }
});
</script>