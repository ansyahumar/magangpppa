@extends('admin.layouts.app')
<link rel="icon" type="image/x-icon"
      href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
    <title>Manajemen Akun</title>
@section('content')
<style>
    [x-cloak] { display: none !important; }

    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
        opacity: 0;
        animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
</style>

<div class="p-6" x-data="{ 
    openModal: false, 
    editMode: false,
    loading: false,
    userData: { id: null, name: '', email: '', role: 'user' },
    
    initAdd() {
        this.editMode = false;
        this.userData = { id: null, name: '', email: '', role: 'user' };
        this.openModal = true;
    },
    
    initEdit(user) {
        this.editMode = true;
        this.userData = { ...user };
        this.openModal = true;
    },

    async submitForm() {
        this.loading = true;
        const form = document.getElementById('userFormMain');
        const formData = new FormData(form);
        
       const url = this.editMode ? `/admin/users/${this.userData.id}` : '{{ route('admin.users.store') }}';
        
       if (this.editMode) formData.append('_method', 'PUT');

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                   'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const res = await response.json();

            if (response.ok) {
                this.openModal = false;
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Data berhasil disimpan', timer: 1500, showConfirmButton: false });
                location.reload();
            } else {
                let errorMsg = res.message || 'Terjadi kesalahan.';
                if (res.errors) errorMsg = Object.values(res.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Gagal', html: errorMsg });
            }
        } catch (e) {
            console.error(e);
            Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Gagal terhubung ke server.' });
        } finally {
            this.loading = false;
        }
    },

    async confirmDelete(id, name) {
        const result = await Swal.fire({
            title: 'Hapus User?',
            text: `Akun ${name} akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/admin/users/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const res = await response.json();
                if (response.ok) {
                    await Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.' });
            }
        }
    }
}">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 animate-in delay-1">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Akun User</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola daftar pengguna dan hak akses sistem SPBE.</p>
        </div>
        <button @click="initAdd()" class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-blue-500/30 flex items-center gap-2 group transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-500 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User Baru
        </button>
    </div>

    <div :class="loading ? 'animate-pulse opacity-50' : ''" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden animate-in delay-2 transition-all duration-500">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                    @foreach($users as $user)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-gray-700/60 transition-all duration-300 group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center rounded-xl font-bold shadow-sm group-hover:scale-105 transition-transform">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:translate-x-1 transition-transform">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full 
                                {{ $user->role === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                                   ($user->role === 'verifikator' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 
                                   'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400') }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center gap-2">
                                <button @click="initEdit({{ json_encode($user) }})" class="p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm active:scale-90 group/btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/btn:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="confirmDelete('{{ $user->id }}', '{{ $user->name }}')" class="p-2.5 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-xl transition-all shadow-sm active:scale-90 group/btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 animate-in delay-3">
        {{ $users->links() }}
    </div>

    <template x-teleport="body">
        <div x-show="openModal" class="fixed inset-0 z-[99] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openModal = false" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            <div x-show="openModal" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                
                <div class="flex items-center justify-between p-6 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="userFormMain" @submit.prevent="submitForm" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="userData.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                        <input type="email" name="email" x-model="userData.email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Role Akses</label>
                        <select name="role" x-model="userData.role" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                            <option value="user">User</option>
                            <option value="verifikator">Verifikator</option>
                            <option value="admin">Administrator</option>
                            <option value="p1">P1</option>
                            <option value="p2">P2</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="editMode ? 'Password (Kosongkan jika tidak ganti)' : 'Password'"></span>
                        </label>
                        <input type="password" name="password" :required="!editMode" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="openModal = false" class="px-5 py-2.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition font-semibold active:scale-95">
                            Batal
                        </button>
                        <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition active:scale-95 disabled:opacity-50 flex items-center gap-2">
                            <span x-text="loading ? 'Memproses...' : (editMode ? 'Update Data' : 'Simpan Data')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection