<div x-show="openModal === 'add-indikator-{{ $a->id_aspek }}'" x-cloak>
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center" 
         @click.self="openModal = null">
        
        <div class="bg-white p-6 rounded-xl w-full max-w-md" @click.stop>
            <h2 class="text-lg font-bold mb-4">Tambah Indikator</h2>

            <form method="POST" action="{{ route('admin.master.indikator.store') }}">
                @csrf
                <input type="hidden" name="id_aspek" value="{{ $a->id_aspek }}">

                <label class="block text-sm font-medium mb-1">Nama Indikator</label>
                <input type="text" name="nama_indikator" placeholder="Masukkan Nama Indikator" 
                       class="w-full border rounded p-2 mb-3" required>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="openModal=null" class="px-3 py-1 bg-gray-300 rounded">Batal</button>
                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>