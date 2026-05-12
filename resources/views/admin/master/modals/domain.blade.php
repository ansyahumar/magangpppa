@php
    $overlay = "fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[999]";
    $box = "bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden relative";
@endphp

<div 
    x-show="openModal === 'add-domain'" 
    x-cloak 
    class="{{ $overlay }}"
    @click="openModal = null" 
>
    <div 
        class="{{ $box }} p-6" 
        @click.stop
    >
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold dark:text-white">Tambah Domain Baru</h2>
            <button @click="openModal = null" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.master.domain.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahunDipilih }}">
            <input type="hidden" name="modul" value="{{ $modul }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Domain</label>
                    <input type="text" name="nama_domain" 
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" 
                           placeholder="Masukkan nama domain..." required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bobot Domain (%)</label>
                    <input type="number" step="0.01" name="bobot" 
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" 
                           placeholder="0.00" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" @click="openModal = null"
                        class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 shadow-lg transition active:scale-95">
                    Simpan Domain
                </button>
            </div>
        </form>
    </div>
</div>

@foreach($domain as $d)
    <template x-if="openModal === 'edit-domain-{{ $d->id_domain }}'">
        <div class="{{ $overlay }}" @click="openModal = null">
            <div class="{{ $box }} p-6" @click.stop>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold dark:text-white">Edit Domain</h2>
                    <button @click="openModal = null" class="text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.master.domain.update', $d->id_domain) }}" method="POST">
                    @csrf 
                    @method('PUT')
                    <input type="hidden" name="modul" value="{{ $modul }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Domain</label>
                            <input type="text" name="nama_domain" value="{{ $d->nama_domain }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bobot Domain (%)</label>
                            <input type="number" step="0.01" name="bobot" value="{{ $d->bobot->bobot ?? 0 }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="openModal = null" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl shadow-lg transition active:scale-95">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <div 
        x-show="openModal === 'delete-domain-{{ $d->id_domain }}'" 
        x-cloak 
        class="{{ $overlay }}"
        @click="openModal = null"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
    >
        <div 
            class="{{ $box }} p-8 text-center" 
            @click.stop
        >
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30 mb-6 text-red-600">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Hapus Domain?</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 px-2">
                Apakah Anda yakin ingin menghapus <strong>{{ $d->nama_domain }}</strong>? <br>
                <span class="text-red-500 text-sm italic">Tindakan ini juga akan menghapus semua aspek dan indikator di bawahnya.</span>
            </p>

            <form action="{{ route('admin.master.domain.delete', $d->id_domain) }}" method="POST" class="flex gap-3">
                @csrf 
                @method('DELETE')
                
                <button type="button" @click="openModal = null" 
                        class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                    Batal
                </button>
                
                <button type="submit" 
                        class="flex-1 bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 shadow-lg shadow-red-500/30 transition active:scale-95">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
@endforeach