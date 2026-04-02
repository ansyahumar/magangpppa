@php
$overlay = "fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-sm p-4";
$box = "bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden";
@endphp

<div x-data="indikatorHandler()" 
     @open-modal-edit.window="openEdit($event.detail)"
     @open-modal-delete.window="openDelete($event.detail)"
     @open-modal-add.window="openAdd($event.detail)">

    <div x-show="openModal" x-cloak class="{{ $overlay }}">
        <template x-if="openModal">
            <div @click.away="closeModal()" class="{{ $box }} p-6 animate-main" x-transition:enter="transition ease-out duration-300">
                <h3 class="text-lg font-bold mb-4" x-text="mode === 'add' ? 'Tambah Indikator' : (mode === 'edit' ? 'Edit Indikator' : 'Hapus Indikator')"></h3>
                <div x-show="mode === 'add' || mode === 'edit'">
                    <form :action="mode === 'add' ? '{{ route('admin.master.indikator.store') }}' : '/admin/indikator/' + form.id_indikator + '/update'" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                        <input type="hidden" name="id_aspek" :value="form.id_aspek">

                        <div class="mb-4">
                            <label class="text-xs font-bold text-gray-500 uppercase">Nama Indikator</label>
                            <textarea name="nama_indikator" x-model="form.nama_indikator" class="w-full border border-gray-300 rounded-xl p-3 mt-1 focus:ring-2 focus:ring-blue-500 outline-none" rows="4" required></textarea>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 active:scale-95">Simpan</button>
                        </div>
                    </form>
                </div>

                <div x-show="mode === 'delete'">
                    <form :action="'/admin/indikator/' + form.id_indikator + '/delete'" method="POST">
                        @csrf
                        @method('DELETE')
                        <p class="mb-6">Yakin ingin menghapus indikator <span class="font-bold text-red-600" x-text="form.nama_indikator"></span>?</p>
                        <div class="flex gap-3">
                            <button type="button" @click="closeModal()" class="flex-1 bg-gray-100 py-2.5 rounded-xl font-bold hover:bg-gray-200">Batal</button>
                            <button type="submit" class="flex-1 bg-red-600 text-white py-2.5 rounded-xl font-bold shadow-lg shadow-red-500/30 active:scale-95">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    if (typeof indikatorHandler !== 'function') {
        function indikatorHandler() {
            return {
                openModal: false,
                mode: '',
                form: { id_indikator: null, id_aspek: null, nama_indikator: '' },
                closeModal() { this.openModal = false; },
                openAdd(detail) {
                    this.mode = 'add';
                    this.form = { id_indikator: null, id_aspek: detail.id_aspek, nama_indikator: '' };
                    this.openModal = true;
                },
                openEdit(detail) {
                    this.mode = 'edit';
                    this.form = { ...detail };
                    this.openModal = true;
                },
                openDelete(detail) {
                    this.mode = 'delete';
                    this.form = { ...detail };
                    this.openModal = true;
                }
            }
        }
    }
</script>