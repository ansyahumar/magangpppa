@extends('admin.layouts.app')

@include('layouts.fav')
    <title>Master Data</title>

@section('content')

<div class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border mb-6">
    <div>
        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Kelola Tahun</label>
        <form action="{{ route('admin.master') }}" method="GET" id="form-tahun">
            <select name="tahun" onchange="this.form.submit()" class="rounded-lg border-gray-300 font-bold text-blue-600">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $tahunDipilih == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @php 
        $tahunTerakhir = $availableYears->max();
        $tahunNext = $tahunTerakhir + 1;
    @endphp

    <div class="ml-auto">
        <form action="{{ route('admin.master.copy') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahunNext }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                <i class="fa-solid fa-copy"></i>
                Buka & Salin Struktur ke {{ $tahunNext }}
            </button>
        </form>
    </div>
</div>
<style>
    [x-cloak] { display: none !important; }
    
    @keyframes fadeInUp {
        from { transform: translateY(15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-main { animation: fadeInUp 0.5s ease-out forwards; }

    @keyframes pulse-blue {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    .btn-pulse { animation: pulse-blue 2s infinite; }

    .collapse-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<div
    x-data="{
        openModal: null,
        activeIndikator: { id: null, nomor: '-', nama: '', kriteria: [] },
        activeAspek: { id: null, nama: '' },
        mode: 'view',
        formData: { penjelasan_kriteria: '', tatacara_penilaian: '', deskripsi: '' },
        tahunAktif: '{{ $tahunDipilih }}',
        
        openDomains: JSON.parse(localStorage.getItem('spbe_open_domains') || '[]'), 
        openAspeks: JSON.parse(localStorage.getItem('spbe_open_aspeks') || '[]'),
        
        toggleDomain(id) {
            id = parseInt(id);
            if (this.openDomains.includes(id)) {
                this.openDomains = this.openDomains.filter(i => i !== id);
            } else {
                this.openDomains.push(id);
            }
            localStorage.setItem('spbe_open_domains', JSON.stringify(this.openDomains));
        },
        expandAll() {
    this.openDomains = @js($domain->pluck('id_domain')).map(id => parseInt(id));
    this.openAspeks = @js($domain->flatMap->aspek->pluck('id_aspek')).map(id => parseInt(id));
    
    localStorage.setItem('spbe_open_domains', JSON.stringify(this.openDomains));
    localStorage.setItem('spbe_open_aspeks', JSON.stringify(this.openAspeks));
},
collapseAll() {
    this.openDomains = [];
    this.openAspeks = [];
    
    localStorage.setItem('spbe_open_domains', JSON.stringify([]));
    localStorage.setItem('spbe_open_aspeks', JSON.stringify([]));
},
        
        toggleAspek(id) {
            id = parseInt(id);
            if (this.openAspeks.includes(id)) {
                this.openAspeks = this.openAspeks.filter(i => i !== id);
            } else {
                this.openAspeks.push(id);
            }
            localStorage.setItem('spbe_open_aspeks', JSON.stringify(this.openAspeks));
        }
    }"
     x-init="
        @if(session('open_domain'))
            let sessDom = {{ session('open_domain') }};
            if(!openDomains.includes(sessDom)) {
                openDomains.push(sessDom);
                localStorage.setItem('spbe_open_domains', JSON.stringify(openDomains));
            }
        @endif
        @if(session('open_aspek'))
            let sessAsp = {{ session('open_aspek') }};
            if(!openAspeks.includes(sessAsp)) {
                openAspeks.push(sessAsp);
                localStorage.setItem('spbe_open_aspeks', JSON.stringify(openAspeks));
            }
        @endif
    @if(session('open_aspek'))
        $nextTick(() => {
            const scrollPos = localStorage.getItem('spbe_scroll_pos');
            if (scrollPos) window.scrollTo(0, scrollPos);
        });
    @endif
    "
    class="space-y-6 p-4 animate-main">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Master Data SPBE</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola Domain, Aspek, dan Indikator Penilaian SPBE.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button @click="expandAll()" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow-md transition-all active:scale-95">
                Expand Semua
            </button>
            <button @click="collapseAll()" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow-md transition-all active:scale-95">
                Collapse Semua
            </button>
            <button @click="openModal = 'add-domain'" class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-500 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>                Tambah Domain
            </button>
        </div>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms class="p-4 bg-emerald-100 text-emerald-700 rounded-xl shadow-sm border border-emerald-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 animate-bounce" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                {{ session('success') }}
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif

 @php 
    $globalAspekCount = 1; 
    $globalIndikatorCount = 1; 
@endphp

<div id="domain-container" class="space-y-6">
    @foreach($domain as $d)
    <div class="domain-item bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:shadow-md" data-domain-id="{{ $d->id_domain }}">
        
        <div @click="toggleDomain({{ $d->id_domain }})" class="p-5 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/40 cursor-pointer hover:bg-blue-50/30 dark:hover:bg-gray-700/50 transition-all group">
            <div class="flex items-center gap-4">
                <div @click.stop class="drag-handle cursor-move p-2 text-gray-400 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                </div>

                <span class="flex h-9 w-auto flex-shrink-0 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-black text-white shadow-sm">
    DOMAIN {{ $loop->iteration }}
</span>

                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-blue-600 transition-colors">
                    {{ $d->nama_domain }}
                </h3>
            </div>

            <div class="flex items-center gap-2" @click.stop>
                <button @click="openModal='edit-domain-{{ $d->id_domain }}'" class="p-2.5 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white dark:text-blue-400 dark:bg-blue-900/20 dark:hover:bg-blue-600 dark:hover:text-white rounded-xl transition-all duration-300 active:scale-90 shadow-sm group/btn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover/btn:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button @click="openModal='delete-domain-{{ $d->id_domain }}'" class="p-2 text-red-600 hover:bg-red-100 rounded-xl transition-all hover:scale-110 active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>

        <div x-show="openDomains.includes({{ $d->id_domain }})" x-transition:enter="transition ease-out duration-300" class="p-6 border-t border-gray-100 dark:border-gray-700 space-y-6">
            <button @click="openModal='add-aspek-{{ $d->id_domain }}'" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all active:scale-95 flex items-center gap-2 shadow-md hover:shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Aspek
            </button>

            <div id="aspek-container-{{ $d->id_domain }}" class="grid grid-cols-1 gap-4 sortable-aspek" data-domain-id="{{ $d->id_domain }}">
                @foreach($d->aspek as $a)
                <div class="aspek-item" data-aspek-id="{{ $a->id_aspek }}">
                    <div @click="toggleAspek({{ $a->id_aspek }})" class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group/aspek">
                        <div class="flex items-center gap-3">
                            <div @click.stop class="drag-handle cursor-move text-gray-300">⠿</div>

                            <span class="flex h-7 w-fit min-w-[1.75rem] items-center justify-center rounded-md bg-emerald-500 px-2 text-[10px] font-bold text-white shadow-sm">
    ASPEK {{ $globalAspekCount++ }}
</span>

                            <div>
                                <h4 class="font-bold text-gray-800 dark:text-gray-200 group-hover/aspek:text-blue-600 transition-colors">{{ $a->nama_aspek }}</h4>
                            </div>
                        </div>
                        <div class="flex gap-1" @click.stop>
                            <button @click="openModal='edit-aspek-{{ $a->id_aspek }}'" class="p-2 text-blue-600 bg-blue-50 rounded-xl"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button @click="openModal='delete-aspek-{{ $a->id_aspek }}'" class="p-2 text-red-500 bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </div>

                   <div x-show="openAspeks.includes({{ $a->id_aspek }})" x-transition class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/40">
                        <button @click="openModal='add-indikator-{{ $a->id_aspek }}'" class="mb-4 px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 rounded-lg text-xs font-bold border border-gray-200">+ Tambah Indikator</button>
                        <div id="indikator-container-{{ $a->id_aspek }}" class="space-y-3 sortable-indikator" data-aspek-id="{{ $a->id_aspek }}">
                            @foreach($a->indikator as $i)
                            <div class="indikator-item-drag group/item p-3 rounded-xl border border-transparent hover:border-gray-200 hover:bg-white transition-all" 
     :id="'indikator-' + {{ $i->id_indikator }}" 
     data-indikator-id="{{ $i->id_indikator }}">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <div class="flex gap-3 items-center">
            <span class="flex h-8 w-fit flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 px-3 text-xs font-black text-blue-600">
    INDIKATOR {{ $globalIndikatorCount++ }}
</span>
            <span class="text-gray-800 dark:text-gray-100 text-sm font-medium leading-relaxed">
                {{ $i->nama_indikator }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <button @click="activeIndikator = { 
                        id: {{ $i->id_indikator }}, 
                        nomor: {{ $globalIndikatorCount - 1 }}, 
                        nama: '{{ addslashes($i->nama_indikator) }}', 
                        penjelasan: @js($i->penjelasan) 
                    }; openModal = 'penjelasan'; $dispatch('open-penjelasan-modal');" 
                    class="px-3 py-2 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-[11px] font-bold hover:bg-blue-600 hover:text-white transition-all duration-200 shadow-sm active:scale-95">
                Penjelasan
            </button>

           <button type="button"
        @click="
            activeIndikator = { 
                id_indikator: {{ $i->id_indikator }}, 
                nama: '{{ addslashes($i->nama_indikator) }}', 
                kriteria: [] 
            }; 
            fetch(`/admin/kriteria/master/${activeIndikator.id_indikator}?tahun={{ $tahunDipilih }}`)
                .then(res => res.json())
                .then(data => {
                    activeIndikator.kriteria = data.kriteria;
                    openModal = 'kriteria';
                    $dispatch('open-kriteria-modal');
                });
        " 
        class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-[11px] font-bold hover:bg-indigo-700 transition-all active:scale-95">
    Kriteria
</button>

           <button @click="$dispatch('open-modal-edit', { 
            id_indikator: {{ $i->id_indikator }}, 
            id_aspek: {{ $a->id_aspek }}, 
            nama_indikator: '{{ addslashes($i->nama_indikator) }}' 
        })" 
        class="px-3 py-2 bg-amber-50 ...">
    Edit
</button>

            <button @click="$dispatch('open-modal-delete', { 
            id_indikator: {{ $i->id_indikator }}, 
            nama_indikator: '{{ addslashes($i->nama_indikator) }}' 
        })" 
        class="px-3 py-2 bg-red-50 ...">
    Hapus
</button>
        </div>
    </div>
</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
<template x-teleport="body">
    <div id="modal-container">
        @include('admin.master.modals.kriteria')
        @include('admin.master.modals.penjelasan')
        @include('admin.master.modals.indikator')

        @foreach($domain as $d)
            @include('admin.master.modals.domain', ['d' => $d])

            @foreach($d->aspek as $a)
                @include('admin.master.modals.aspek', ['a' => $a, 'd' => $d])
            @endforeach
        @endforeach
        @foreach($domain as $d)
    @include('admin.master.modals.domain', ['d' => $d])
    @include('admin.master.modals.aspek-add', ['d' => $d])

    @foreach($d->aspek as $a)
        @include('admin.master.modals.aspek', ['a' => $a, 'd' => $d])
        @include('admin.master.modals.indikator-add', ['a' => $a]) 
    @endforeach
@endforeach
    </div>
</template>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function refreshSemuaNomor() {
        document.querySelectorAll('.nomor-domain-badge').forEach((badge, i) => {
            badge.innerText = i + 1;
        });

        document.querySelectorAll('.nomor-aspek-badge').forEach((badge, i) => {
            badge.innerText = i + 1;
        });

        document.querySelectorAll('.nomor-indikator-badge').forEach((badge, i) => {
            badge.innerText = i + 1;
        });
    }

    const domainContainer = document.getElementById('domain-container');
    if (domainContainer) {
        new Sortable(domainContainer, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-blue-50',
            onEnd: function (evt) {
                const orderIds = Array.from(domainContainer.querySelectorAll('.domain-item'))
                                     .map(el => el.getAttribute('data-domain-id'));
                
                updatePosition('/admin/master/domain/move', { order: orderIds });
                refreshSemuaNomor();
            }
        });
    }

    const initAspekSortable = () => {
        document.querySelectorAll('.sortable-aspek').forEach(el => {
            new Sortable(el, {
                group: 'shared-aspek', 
                animation: 150,
                handle: '.drag-handle',
                filter: 'button',
                ghostClass: 'bg-blue-50',
                onEnd: function (evt) {
                    const aspekId = evt.item.getAttribute('data-aspek-id');
                    const newDomainId = evt.to.getAttribute('data-domain-id');
                    
                    updatePosition('/admin/master/aspek/move', {
                        id_aspek: aspekId,
                        id_domain: newDomainId
                    });
                    refreshSemuaNomor();
                }
            });
        });
    }

    const initIndikatorSortable = () => {
        document.querySelectorAll('.sortable-indikator').forEach(el => {
            new Sortable(el, {
                group: 'shared-indikator',
                animation: 150,
                ghostClass: 'bg-emerald-50',
                onEnd: function (evt) {
                    const indikatorId = evt.item.getAttribute('data-indikator-id');
                    const newAspekId = evt.to.getAttribute('data-aspek-id');
                    
                    updatePosition('/admin/master/indikator/move', {
                        id_indikator: indikatorId,
                        id_aspek: newAspekId
                    });
                    refreshSemuaNomor();
                }
            });
        });
    }

    initAspekSortable();
    initIndikatorSortable();

    async function updatePosition(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                const result = await response.json();
                throw new Error(result.error || 'Server Error');
            }
            console.log('Urutan berhasil disimpan ke database');
        } catch (error) {
            console.error('Gagal memindahkan data:', error);
            alert('Gagal menyimpan urutan baru ke server.');
        }
    }
});
</script>
<script>
document.addEventListener('submit', function (e) {
    if (e.target.id === 'formPenjelasanAdmin') {
        localStorage.setItem('spbe_scroll_pos', window.scrollY);
    }
});

window.addEventListener('load', function() {
    const scrollPos = localStorage.getItem('spbe_scroll_pos');
    if (scrollPos) {
        window.requestAnimationFrame(() => {
            window.scrollTo({
                top: parseInt(scrollPos),
                behavior: 'instant'
            });
            localStorage.removeItem('spbe_scroll_pos');
        });
    }
});
</script>
</div>
@endsection