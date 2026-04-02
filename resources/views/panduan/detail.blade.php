<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<head>
<style>
     .ck-editor__editable { min-height: 200px !important; max-height: 400px !important; }
    .ck.ck-editor { width: 100% !important; }
    .content-preview table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    .content-preview table td, .content-preview table th { border: 1px solid #ddd; padding: 8px; }
</style>
</head>
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/60 overflow-hidden">
            
            <div class="p-8 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-700 p-4 rounded-2xl shadow-lg shadow-blue-200 w-16 h-16 flex items-center justify-center">
                        <i class="fa-solid fa-book-bookmark text-white text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-widest px-2 py-1 bg-blue-50 rounded-md">Master Indikator</span>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-2 leading-tight">
                            {{ $nama_indikator }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold mb-6 
                    @if($tipe == 'penjelasan') bg-indigo-100 text-indigo-700 @else bg-emerald-100 text-emerald-700 @endif">
                    <span class="relative flex h-2 w-2 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full @if($tipe == 'penjelasan') bg-indigo-400 @else bg-emerald-400 @endif opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 @if($tipe == 'penjelasan') bg-indigo-500 @else bg-emerald-500 @endif"></span>
                    </span>
                    @if($tipe == 'penjelasan') Penjelasan Kriteria @else Tata Cara Penilaian @endif
                </div>

                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r @if($tipe == 'penjelasan') from-indigo-500 to-blue-500 @else from-emerald-500 to-teal-500 @endif rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
                    <div class="relative p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
                       <div class="relative p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
    <div class="text-gray-700 text-lg leading-relaxed content-preview">
        @if($tipe == 'penjelasan')
            {!! $data->penjelasan_kriteria ?? 'Informasi kriteria belum tersedia.' !!}
        @else
            {!! $data->tatacara_penilaian ?? 'Informasi tata cara belum tersedia.' !!}
        @endif
    </div>
</div>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex justify-end">
    <button onclick="closeTabOrGoBack()" 
       class="flex items-center px-8 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm active:scale-95 cursor-pointer">
        <i class="fa-solid fa-xmark mr-2"></i>
        Tutup Halaman
    </button>
</div>

<script>
function closeTabOrGoBack() {
    
    window.close();
    if (!window.closed) {
        window.history.back();
    }
}
</script>
        </div>

    </div>
</div>