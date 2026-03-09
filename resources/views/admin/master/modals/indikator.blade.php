@php
   $overlay = "fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-[2px] p-4";
    $box = "bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden";
@endphp

<style>
    .btn-rotate-target {
        transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .btn-rotate-trigger:hover .btn-rotate-target {
        transform: rotate(90deg);
    }
</style>


@foreach($aspek as $a)
<div x-show="openModal === 'add-indikator-{{ $a->id_aspek }}'" 
     x-cloak 
     x-transition:enter="transition ease-out duration-100"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-75"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="openModal = null"
     class="{{ $overlay }}">
    
    
    <div @click.away="openModal = null" @click.stop
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-98 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="{{ $box }}">
        
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 flex justify-between items-center">
            <div class="text-left">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Tambah Indikator</h3>
                <p class="text-[10px] text-blue-600 dark:text-blue-400 font-black uppercase tracking-widest mt-0.5">Aspek: {{ $a->nama_aspek }}</p>
            </div>
            <button @click="openModal = null" class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors active:scale-75">
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-rotate-target h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.master.indikator.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="id_aspek" value="{{ $a->id_aspek }}">

            <div class="space-y-4 text-left">
            
                <div>
                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1.5 tracking-widest">Nama Indikator</label>
                    <textarea name="nama_indikator" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all resize-none font-medium" 
                              rows="4" placeholder="Masukkan nama indikator..." required></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t dark:border-gray-700 pt-6">
                <button type="button" @click="openModal=null" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-800 transition-all">Batal</button>
                <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-100 active:scale-95 transition-all">Simpan Indikator</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@foreach($aspek as $a)
    @foreach($a->indikator as $i)
        <div x-show="openModal === 'edit-indikator-{{ $i->id_indikator }}'" 
             x-cloak 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="openModal = null"
             class="{{ $overlay }}">
            
            <div @click.away="openModal = null" @click.stop
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-98 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="{{ $box }}">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 flex justify-between items-center">
                    <div class="text-left">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Edit Indikator</h3>
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black uppercase tracking-widest mt-0.5">Aspek: {{ $a->nama_aspek }}</p>
                    </div>
                    <button @click="openModal = null" class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors active:scale-75">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-rotate-target h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.master.indikator.update', $i->id_indikator) }}" method="POST" class="p-6">
                    @csrf @method('PUT')
                    <input type="hidden" name="id_aspek" value="{{ $a->id_aspek }}">
                    
                    <div class="space-y-5 text-left">
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-1.5 tracking-widest">Nama Indikator</label>
                            <textarea name="nama_indikator" 
                                      class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all resize-none font-medium" 
                                      rows="4">{{ $i->nama_indikator }}</textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t dark:border-gray-700 pt-6">
                        <button type="button" @click="openModal = null" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-800 transition-all">Batal</button>
                        <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 active:scale-95 transition-all flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
     
        <div x-show="openModal === 'delete-indikator-{{ $i->id_indikator }}'" 
             x-cloak 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 backdrop-blur-[2px] p-4">
            
            <div @click.away="openModal = null"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-98 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl border border-gray-100 dark:border-gray-700 transform transition-all">
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-800 dark:text-white">Hapus Indikator?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 px-4">
                        Data <span class="font-bold text-gray-800 dark:text-gray-200">"{{ $i->nama_indikator }}"</span> akan dihapus permanen.
                    </p>
                </div>

                <form action="{{ route('admin.master.indikator.delete', $i->id_indikator) }}" method="POST" class="mt-6 flex gap-3">
                    @csrf @method('DELETE')
                    <button type="button" @click="openModal = null" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 py-3 rounded-xl font-bold transition-all active:scale-95">Batal</button>
                    <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 shadow-lg shadow-red-200 active:scale-95">Ya, Hapus</button>
                </form>
            </div>
        </div>
    @endforeach
@endforeach