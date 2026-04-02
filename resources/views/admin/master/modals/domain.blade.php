@php
    $overlay = "fixed inset-0 bg-slate-900/50 flex items-center justify-center z-[999]";
    $box = "bg-white dark:bg-gray-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden relative";
@endphp

<div x-show="openModal === 'add-domain'" x-cloak class="{{ $overlay }}">
    <div class="{{ $box }} p-6">
        <h2 class="text-lg font-bold mb-4 dark:text-white">Tambah Domain</h2>

        <form action="{{ route('admin.master.domain.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahunDipilih }}">

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Nama Domain</label>
                <input type="text" name="nama_domain" 
                       class="w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="Contoh: Domain Kebijakan Internal" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Bobot Domain (%)</label>
                <input type="number" step="0.01" name="bobot" 
                       class="w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="0.00" required>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="openModal=null"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

@foreach($domain as $d)
    <template x-if="openModal === 'edit-domain-{{ $d->id_domain }}'">
        <div class="{{ $overlay }}" x-cloak>
            <div class="{{ $box }} p-6">
                <h2 class="font-bold text-lg mb-4 dark:text-white">Edit Domain & Bobot</h2>

                <form action="{{ route('admin.master.domain.update', $d->id_domain) }}" method="POST">
                    @csrf 
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Nama Domain</label>
                        <input type="text" name="nama_domain"
                               value="{{ $d->nama_domain }}"
                               class="w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1 dark:text-gray-300">Bobot Domain (%)</label>
                        <input type="number" step="0.01" name="bobot"
                               value="{{ $d->bobot->bobot ?? 0 }}"
                               class="w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="openModal=null"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-lg">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <div x-show="openModal === 'delete-domain-{{ $d->id_domain }}'" x-cloak class="{{ $overlay }}">
        <div class="{{ $box }}">
            <div class="p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-4 text-red-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h5 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Hapus Domain?</h5>
                <p class="text-gray-500 dark:text-gray-400 text-sm px-2">
                    Menghapus <strong>{{ $d->nama_domain }}</strong> akan menghapus semua aspek, indikator & kriteria di bawahnya secara permanen.
                </p>

                <form action="{{ route('admin.master.domain.delete', $d->id_domain) }}" method="POST" class="mt-6 flex gap-3">
                    @csrf 
                    @method('DELETE')
                    <button type="button" @click="openModal = null" 
                            class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" 
                            class="flex-1 bg-red-600 text-white py-2.5 rounded-xl font-semibold hover:bg-red-700 shadow-lg transition">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endforeach