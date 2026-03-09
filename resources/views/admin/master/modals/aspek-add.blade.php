@php
    $inputStyle = "w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all";
    $btnCancelStyle = "px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-xl transition-all";
@endphp

<div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
    <h5 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Aspek Baru</h5>
    <div class="mt-1 flex items-center gap-2">
        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold rounded uppercase">
            Domain {{ $d->nomor_domain ?? '' }}
        </span>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
            {{ $d->nama_domain }}
        </p>
    </div>
</div>

<form action="{{ route('admin.master.aspek.store') }}" method="POST" class="p-6">
    @csrf
    
    <input type="hidden" name="id_domain" value="{{ $d->id_domain }}">
    <input type="hidden" name="tahun" value="{{ $d->tahun }}">

    <div class="space-y-4">
        <div>
            <label class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Aspek</label>
            <input type="text" name="nama_aspek" placeholder="Masukkan nama aspek..." class="{{ $inputStyle }}" required>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Target (0-5)</label>
                <input type="number" name="target" min="0" max="5" step="0.01" placeholder="0.00" class="{{ $inputStyle }}" required>
            </div>
            <div>
                <label class="block mb-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300">Bobot Aspek (%)</label>
                <input type="number" name="bobot" min="0" max="100" step="0.01" placeholder="0.00" class="{{ $inputStyle }}" required>
            </div>
        </div>
        
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
            <p class="text-[11px] text-blue-700 dark:text-blue-400 leading-relaxed">
                <strong>Catatan:</strong> Aspek ini akan otomatis disimpan untuk periode penilaian <strong>Tahun {{ $d->tahun }}</strong>.
            </p>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-8">
        <button type="button" @click="openModal = null" class="{{ $btnCancelStyle }}">
            Batal
        </button>
        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95">
            Simpan Aspek
        </button>
    </div>
</form>