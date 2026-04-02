@php
    $overlayContainer = "fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto";
    $overlayBg = "fixed inset-0 bg-slate-900/40 backdrop-sm transition-opacity";
    $modalBox = "relative bg-white dark:bg-gray-800 w-full rounded-2xl shadow-2xl border border-white/20 overflow-hidden transform transition-all";
    $inputStyle = "w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-gray-400";
    $btnCancelStyle = "px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors";
@endphp

<div 
    x-show="openModal === 'kriteria'" 
    x-cloak 
    class="{{ $overlayContainer }}"
    x-data="{ 
        mode: 'list', 
        editId: null,
        listIndikator: {{ isset($indicators) ? $indicators->toJson() : '[]' }},
        formData: {
            nama_kriteria: '',
            bobot_nilai: '',
            id_indikator: ''
        },
        resetForm() {
            this.mode = 'list';
            this.editId = null;
            this.formData.nama_kriteria = '';
            this.formData.bobot_nilai = '';
            this.formData.id_indikator = this.activeIndikator?.id_indikator;
        }
    }"
    @open-kriteria-modal.window="resetForm()" 
    @keydown.escape.window="openModal = null"
>
    <div class="{{ $overlayBg }}" 
         @click="openModal = null"
         x-show="openModal === 'kriteria'"
         x-transition:enter="ease-out duration-75"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
    </div>

    <div 
        x-show="openModal === 'kriteria'"
        @click.away="openModal = null" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="{{ $modalBox }} max-w-lg"
    >
        
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight" 
                    x-text="mode === 'list' ? 'Kelola Kriteria' : (mode === 'add' ? 'Tambah Kriteria' : 'Edit Kriteria')"></h3>
                <p class="text-[10px] text-blue-600 dark:text-blue-400 font-black uppercase tracking-widest mt-1">
                    Indikator: <span class="normal-case font-medium opacity-80" x-text="activeIndikator?.nama"></span>
                </p>
            </div>
            <button @click="openModal = null" class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 btn-rotate-target" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div x-show="mode === 'list'">
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <template x-if="!activeIndikator?.kriteria || activeIndikator.kriteria.length === 0">
                    <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-2xl text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <p class="text-sm italic">Belum ada kriteria.</p>
                    </div>
                </template>

                <div class="space-y-3 mt-2">
                    <template x-for="k in activeIndikator?.kriteria" :key="k.id_kriteria">
                        <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl hover:shadow-md hover:border-blue-200 dark:hover:border-blue-900 transition-all group">
                            <div class="flex flex-col gap-1 text-left">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="k.nama_kriteria"></span>
                                <span class="inline-flex w-fit items-center px-2 py-0.5 rounded text-[10px] font-black bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                    Bobot: <span x-text="k.bobot_nilai"></span>%
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button @click="mode = 'edit'; editId = k.id_kriteria; formData.nama_kriteria = k.nama_kriteria; formData.bobot_nilai = k.bobot_nilai; formData.id_indikator = k.id_indikator" 
                                        class="p-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all active:scale-90 shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <form :action="`/admin/kriteria/${k.id_kriteria}/delete`" method="POST" onsubmit="return confirm('Hapus kriteria ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2.5 bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-500 hover:text-white rounded-lg transition-all active:scale-90 shadow-sm flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/80 dark:bg-gray-900/30">
                <button @click="openModal = null" class="text-[11px] font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest transition-colors">Tutup</button>
                <button @click="mode = 'add'; formData.nama_kriteria = ''; formData.bobot_nilai = ''; formData.id_indikator = activeIndikator?.id_indikator" 
                        class="group relative px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Kriteria
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </button>
            </div>
        </div>

        <div x-show="mode !== 'list'" class="p-6">
            <form :action="mode === 'edit' ? `/admin/kriteria/${editId}/update` : '{{ route('kriteria.store') }}'" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="id_indikator" :value="formData.id_indikator">
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 ml-1 text-left">Nama Kriteria</label>
                        <textarea name="nama_kriteria" x-model="formData.nama_kriteria" class="{{ $inputStyle }} min-h-[100px] resize-none" rows="3" placeholder="Contoh: Kualitas dokumen pendukung..." required></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 ml-1 text-left">Bobot Nilai (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="bobot_nilai" x-model="formData.bobot_nilai" class="{{ $inputStyle }}" placeholder="0.00" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t dark:border-gray-700 pt-6">
                    <button type="button" @click="mode = 'list'" class="{{ $btnCancelStyle }}">Batal</button>
                    <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 active:scale-95 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

    .btn-rotate-target {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .btn-rotate-trigger:hover .btn-rotate-target {
        transform: rotate(90deg);
    }
</style>