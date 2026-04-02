
@php
    $overlayContainer = "fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto";
    $overlayBg = "fixed inset-0 bg-slate-900/40 backdrop-sm transition-opacity";
    $modalBox = "relative bg-white dark:bg-gray-800 w-full rounded-2xl shadow-2xl border border-white/20 overflow-hidden transform transition-all";
    $inputStyle = "w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-gray-400";
    $btnCancelStyle = "px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors";
@endphp
<div 
    x-show="openModal === 'add-aspek-{{ $d->id_domain }}'" 
    x-cloak 
    class="fixed inset-0 z-[999] flex items-center justify-center p-4"
    
><div class="{{ $overlayBg }}" @click="openModal = null"></div>
    <div 
        x-show="openModal === 'add-aspek-{{ $d->id_domain }}'"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        @click="openModal = null"
        class="absolute inset-0">
    </div>

    <div 
        x-show="openModal === 'add-aspek-{{ $d->id_domain }}'"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="relative bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl border border-white/20 overflow-hidden"
        @click.stop
    >
        
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 flex justify-between items-center">
            <div class="text-left">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Tambah Aspek Baru</h3>
                <p class="text-[10px] text-blue-600 dark:text-blue-400 font-black uppercase tracking-widest mt-0.5">
                    Domain {{ $d->nomor_domain ?? '' }}: <span class="normal-case font-medium opacity-80">{{ $d->nama_domain }}</span>
                </p>
            </div>
            
            <button @click="openModal = null" 
                    class="btn-rotate-trigger group p-2 text-gray-400 hover:text-red-500 transition-colors active:scale-75">
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-rotate-target h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.master.aspek.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="id_domain" value="{{ $d->id_domain }}">
            <input type="hidden" name="tahun" value="{{ $d->tahun }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 ml-1">
                        Nama Aspek
                    </label>
                    <input type="text" name="nama_aspek" placeholder="Contoh: Kebijakan Internal SPBE" class="{{ $inputStyle }}" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 ml-1">
                            Target (0-5)
                        </label>
                        <input type="number" name="target" min="0" max="5" step="0.01" placeholder="0.00" class="{{ $inputStyle }}" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 ml-1">
                            Bobot (%)
                        </label>
                        <input type="number" name="bobot" min="0" max="100" step="0.01" placeholder="0.00" class="{{ $inputStyle }}" required>
                    </div>
                </div>
                
                <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/50 rounded-xl flex gap-3">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-relaxed italic">
                        Aspek ini akan didaftarkan pada <strong>Tahun Anggaran {{ $d->tahun }}</strong>. Pastikan data sudah sesuai sebelum disimpan.
                    </p>
                </div>
            </div>

            <div class="flex justify-end items-center gap-3 pt-4 border-t dark:border-gray-700">
                <button type="button" @click="openModal = null" class="{{ $btnCancelStyle }}">
                    Batal
                </button>
                <button type="submit" class="px-8 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Aspek
                </button>
            </div>
        </form>
    </div>
</div>

<div x-show="openModal === 'edit-aspek-{{ $a->id_aspek }}'" x-cloak class="{{ $overlayContainer }}">
    <div class="{{ $overlayBg }}" @click="openModal = null"></div>

    <div class="{{ $modalBox }} max-w-md" @click.stop x-show="openModal === 'edit-aspek-{{ $a->id_aspek }}'"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95">
        
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-indigo-50/30">
            <h5 class="text-xl font-bold text-gray-800 dark:text-white">Edit Aspek</h5>
        </div>

        <form action="{{ route('aspek.update', $a->id_aspek) }}" method="POST" class="p-6">
            @csrf @method('PUT')
            
            <div class="flex flex-col gap-4">
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-indigo-600 dark:text-indigo-400">Pindahkan ke Domain</label>
                    <select name="id_domain" class="{{ $inputStyle }} cursor-pointer">
                        @foreach($domain as $d)
                            <option value="{{ $d->id_domain }}" {{ $a->id_domain == $d->id_domain ? 'selected' : '' }}>
                                Domain {{ $d->nomor_domain }}: {{ $d->nama_domain }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 italic">*Pilih domain lain jika ingin memindahkan aspek ini.</p>
                </div>

                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Nama Aspek</label>
                    <input type="text" name="nama_aspek" value="{{ $a->nama_aspek }}" class="{{ $inputStyle }}" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                   <div>
        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Target (0-5)</label>
        <input 
            type="number" 
            name="target" 
            min="0" 
            max="5" 
            step="0.01" 
            value="{{ $a->target }}" 
            class="{{ $inputStyle }}" 
            required
        >
    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Bobot (%)</label>
                        <input type="number" name="bobot" min="0" max="100" step="0.01" value="{{ $a->bobot->bobot ?? 0 }}" class="{{ $inputStyle }}" required>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" @click="openModal = null" class="{{ $btnCancelStyle }}">Batal</button>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg transition-all shadow-indigo-500/30">
                    Update Aspek
                </button>
            </div>
        </form>
    </div>
</div>
<div x-show="openModal === 'delete-aspek-{{ $a->id_aspek }}'" x-cloak class="{{ $overlayContainer }}">
    <div class="{{ $overlayBg }}" @click="openModal = null"></div>

    <div class="{{ $modalBox }} max-w-sm" @click.stop x-show="openModal === 'delete-aspek-{{ $a->id_aspek }}'">
        <div class="p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-4 text-red-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h5 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Hapus Aspek?</h5>
            <p class="text-gray-500 text-sm px-2">
                Menghapus <strong>{{ $a->nama_aspek }}</strong> akan menghapus semua indikator & kriteria di bawahnya secara permanen.
            </p>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-700/30 p-4 flex gap-3">
            <button type="button" @click="openModal = null" class="flex-1 {{ $btnCancelStyle }}">Batal</button>
            <form action="{{ route('aspek.delete', $a->id_aspek) }}" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-lg shadow-red-500/20 transition-all active:scale-95">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>