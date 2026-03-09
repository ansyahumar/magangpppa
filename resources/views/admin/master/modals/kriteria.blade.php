<div
    x-show="openModal === 'kriteria'"
    x-cloak
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-[2px] p-4"
    x-data="{ 
        mode: 'list', 
        editId: null,
        listIndikator: {{ isset($indikators) ? $indikators->toJson() : '[]' }},
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
    <div @click.away="openModal = null" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-98 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all">
        
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white tracking-tight" 
                        x-text="mode === 'list' ? 'Kelola Kriteria' : (mode === 'add' ? 'Tambah Kriteria' : 'Edit Kriteria')"></h3>
                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-layer-group text-indigo-500"></i>
                        Indikator: <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="activeIndikator?.nama"></span>
                    </p>
                </div>
                <button @click="openModal = null" class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors active:scale-75">
                    <i class="fa-solid fa-xmark text-xl btn-rotate-target inline-block"></i>
                </button>
            </div>
        </div>

        <div x-show="mode === 'list'" 
             x-transition:enter="transition duration-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="px-6 py-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
            
            <template x-if="!activeIndikator?.kriteria || activeIndikator.kriteria.length === 0">
                <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                    <i class="fa-solid fa-folder-open text-gray-300 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-400 italic">Belum ada kriteria yang ditambahkan.</p>
                </div>
            </template>

            <ul class="space-y-3">
                <template x-for="k in activeIndikator?.kriteria" :key="k.id_kriteria">
                    <li class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900 transition-all duration-100 group">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="k.nama_kriteria"></span>
                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                <i class="fa-solid fa-chart-pie mr-1"></i> Bobot: <span x-text="k.bobot_nilai"></span>%
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="mode = 'edit'; editId = k.id_kriteria; formData.nama_kriteria = k.nama_kriteria; formData.bobot_nilai = k.bobot_nilai; formData.id_indikator = k.id_indikator" 
                                    class="p-2 bg-amber-100 text-amber-700 hover:bg-amber-500 hover:text-white rounded-lg transition-all active:scale-90 shadow-sm flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            <form :action="`/admin/kriteria/${k.id_kriteria}/delete`" method="POST" onsubmit="return confirm('Hapus kriteria ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 bg-red-100 text-red-700 hover:bg-red-500 hover:text-white rounded-lg transition-all active:scale-90 shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <div x-show="mode !== 'list'" 
             x-transition:enter="transition duration-100"
             x-transition:enter-start="opacity-0 scale-98"
             x-transition:enter-end="opacity-100 scale-100"
             class="px-6 py-6">
<form :action="mode === 'edit' ? `/admin/kriteria/${editId}/update` : '{{ route('kriteria.store') }}'" method="POST">                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <input type="hidden" name="id_indikator" :value="formData.id_indikator">
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-1.5 tracking-widest text-left">Nama Kriteria</label>
                        <textarea 
                            name="nama_kriteria" 
                            x-model="formData.nama_kriteria" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all resize-none" 
                            rows="3" 
                            placeholder="Contoh: Kualitas dokumen..."
                            required></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-1.5 tracking-widest text-left">Bobot Nilai (%)</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                step="0.01" 
                                name="bobot_nilai" 
                                x-model="formData.bobot_nilai" 
                                class="w-full pl-4 pr-12 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" 
                                placeholder="0.00"
                                required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t dark:border-gray-700 pt-6">
                    <button type="button" @click="mode = 'list'" 
                            class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-100 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-check-double text-xs"></i>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>

        <div x-show="mode === 'list'" 
             class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/80 dark:bg-gray-900/30 backdrop-blur-sm">
            <button @click="openModal = null" class="text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">Tutup</button>
            <button @click="mode = 'add'; formData.nama_kriteria = ''; formData.bobot_nilai = ''; formData.id_indikator = activeIndikator?.id_indikator" 
                    class="group relative px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 overflow-hidden">
                <span class="relative z-10 flex items-center gap-2">
                    <i class="fa-solid fa-plus transition-transform group-hover:rotate-90"></i>
                    Tambah Kriteria
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </button>
        </div>
    </div>
</div>

<style>
   
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

  
    .btn-rotate-target {
        transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .btn-rotate-trigger:hover .btn-rotate-target {
        transform: rotate(90deg);
    }
</style>