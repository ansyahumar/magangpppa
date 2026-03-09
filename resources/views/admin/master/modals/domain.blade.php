@php
$overlay = "fixed inset-0 bg-black/50 flex items-center justify-center z-[100]";
$box = "bg-white w-full max-w-md rounded-xl shadow-xl p-6";
@endphp


<div x-show="openModal === 'add-domain'" x-cloak class="{{ $overlay }}">
    <div class="{{ $box }}">
        <h2 class="text-lg font-bold mb-4">Tambah Domain</h2>

        <form action="{{ route('admin.master.domain.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahunDipilih }}">

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Nama Domain</label>
                <input type="text" name="nama_domain" 
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="Contoh: Domain Kebijakan Internal" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Bobot Domain (%)</label>
                <input type="number" step="0.01" name="bobot" 
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="0.00" required>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="openModal=null"
                    class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>


@foreach($domain as $d)


    <div x-show="openModal === 'edit-domain-{{ $d->id_domain }}'" x-cloak class="{{ $overlay }}">
        <div class="{{ $box }}">
            <h2 class="font-bold text-lg mb-4">Edit Domain & Bobot</h2>

            <form action="{{ route('admin.master.domain.update', $d->id_domain) }}" method="POST">
                @csrf 
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Nama Domain</label>
                    <input type="text" name="nama_domain"
                           value="{{ $d->nama_domain }}"
                           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Bobot Domain (%)</label>
                    <input type="number" step="0.01" name="bobot"
                           value="{{ $d->bobot->bobot ?? 0 }}"
                           class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="openModal=null"
                        class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Update</button>
                </div>
            </form>
        </div>
    </div>


    <div x-show="openModal === 'delete-domain-{{ $d->id_domain }}'" x-cloak class="{{ $overlay }}">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Hapus Domain?</h3>
                <p class="text-sm text-gray-500 mt-2">
                    Menghapus domain <strong>{{ $d->nama_domain }}</strong> akan menghapus seluruh data bobot, aspek, dan indikator di bawahnya secara permanen.
                </p>
            </div>
            <form action="{{ route('admin.master.domain.delete', $d->id_domain) }}" method="POST" class="mt-6 flex gap-3">
                @csrf 
                @method('DELETE')
                <button type="button" @click="openModal = null" 
                        class="flex-1 bg-gray-100 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" 
                        class="flex-1 bg-red-600 text-white py-2.5 rounded-xl font-semibold hover:bg-red-700 shadow-lg transition">Ya, Hapus</button>
            </form>
        </div>
    </div>
@endforeach