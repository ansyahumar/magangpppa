@extends('admin.layouts.app')

@section('content')
<div class="p-8 bg-gray-50 min-h-screen" x-data="satuanApp()">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-extrabold text-gray-800">Manajemen Satuan</h1>
        <button @click="openAddModal()" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            + Tambah Satuan
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                <tr>
                    <th class="px-6 py-4">Nama Satuan</th>
                    <th class="px-6 py-4">Unit</th>
                    <th class="px-6 py-4">No. Indikator</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $item)
                <tr class="hover:bg-gray-50/50 transition group">
                    <td class="px-6 py-4 font-bold text-gray-700">{{ $item->satuan }}</td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $item->unit ?? '-' }}</td>
                    <td class="px-6 py-4 text-xs font-mono text-indigo-600">
                        <div x-data="{ expanded: false }" class="max-w-[150px]">
                            <div @click="expanded = !expanded" 
                                 class="cursor-pointer hover:text-indigo-800 transition"
                                 :class="expanded ? 'whitespace-normal' : 'truncate'"
                                 title="Klik untuk detail">
                                {{ $item->no_indikator ?? '-' }}
                            </div>
                        </div>
                    </td>
<td class="px-6 py-4 text-right">
    <div class="flex justify-end gap-2">
        <button @click="openEditModal({{ json_encode($item) }})" 
                class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-blue-600 hover:text-white transition">
            Edit
        </button>

        <form action="{{ route('admin.satuan.destroy', $item->id_satuan) }}" 
      method="POST" 
      class="form-delete inline-block">
    @csrf
    @method('DELETE')
    <button type="submit" 
            class="bg-rose-50 text-rose-600 px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-rose-600 hover:text-white transition">
        Hapus
    </button>
</form>
    </div>
</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 bg-gray-50/50 border-t border-gray-100">
            {{ $data->links() }}
        </div>
    </div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" x-cloak x-transition>
        <div class="bg-white w-full max-w-md rounded-[2rem] shadow-2xl p-8" @click.away="showModal = false">
            <h2 class="text-2xl font-black text-gray-800 mb-6" x-text="editMode ? 'Edit Data' : 'Tambah Data'"></h2>

            <form :action="editMode ? '/admin/satuan/' + form.id_satuan : '{{ route('admin.satuan.store') }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">

                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama Satuan</label>
                        <input type="text" name="satuan" x-model="form.satuan" required class="w-full mt-1 px-5 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Unit</label>
                            <input type="text" name="unit" x-model="form.unit" class="w-full mt-1 px-5 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">No. Indikator</label>
                            <input type="text" name="no_indikator" x-model="form.no_indikator" class="w-full mt-1 px-5 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="showModal = false" class="px-6 py-3 font-bold text-gray-400 hover:text-gray-600 transition">Batal</button>
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">
                        <span x-text="editMode ? 'Simpan Perubahan' : 'Simpan Baru'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    
    function satuanApp() {
        return {
            showModal: false,
            editMode: false,
            form: { id_satuan: '', satuan: '', unit: '', no_indikator: '' },
            
            openAddModal() {
                this.editMode = false;
                this.form = { id_satuan: '', satuan: '', unit: '', no_indikator: '' };
                this.showModal = true;
            },
            
            openEditModal(item) {
                this.editMode = true;
                // Sangat penting: Menangkap id_satuan agar URL action form terbentuk sempurna
                this.form = { 
                    id_satuan: item.id_satuan, 
                    satuan: item.satuan, 
                    unit: item.unit, 
                    no_indikator: item.no_indikator 
                };
                this.showModal = true;
                console.log("Editing ID:", this.form.id_satuan);
            }
        }
    }

    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah form submit otomatis

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48', // Warna rose-600
                cancelButtonColor: '#64748b', // Warna slate-500
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik "Ya", jalankan submit form
                    this.submit();
                }
            });
        });
    });
</script>
@endsection