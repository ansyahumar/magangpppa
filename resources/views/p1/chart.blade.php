@extends('layouts.p1')

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.6s ease-out forwards; }

    .executive-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .executive-card:hover {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        border-color: #3b82f6;
    }

     .card-accent-blue { border-left: 6px solid #1e40af; }
    .card-accent-emerald { border-left: 6px solid #059669; }
    .card-accent-purple { border-left: 6px solid #7c3aed; }
    .card-accent-pink { border-left: 6px solid #db2777; }

    .dark .executive-card {
        background: #1f2937;
        border-color: #374151;
    }

    .formal-select {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        font-weight: 700;
        color: #1e40af;
        cursor: pointer;
    }
</style>

<div class="px-6 py-8 max-w-7xl mx-auto space-y-8 animate-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-200 pb-6 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Dashboard Monitoring <span class="text-blue-700">SPBE</span>
            </h2>
            <p class="text-slate-500 font-medium mt-1">Laporan Capaian Indeks Sistem Pemerintahan Berbasis Elektronik</p>
        </div>
        
        <form id="formTahun" method="GET" action="{{ route('p1.chart') }}">
            <select name="tahun" onchange="this.form.submit()" class="formal-select dark:bg-gray-800 dark:border-gray-700">
                <option value="all" {{ (string)$tahunDipilih === 'all' ? 'selected' : '' }}>RINGKASAN MULTI-TAHUN</option>
                @foreach($tahunList as $th)
                    <option value="{{ $th }}" {{ (string)$tahunDipilih === (string)$th ? 'selected' : '' }}>LAPORAN TAHUN {{ $th }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="executive-card card-accent-blue p-8" onclick="openChartModal('Trend Indeks SPBE', 'mixed')">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-blue-50 text-blue-700 rounded-lg"><i class="fa-solid fa-chart-line"></i></div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wide">Analisis Tren Tahunan</h3>
        </div>
        <div class="h-[350px]"><canvas id="mixedChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="executive-card card-accent-emerald p-8" onclick="openChartModal('Capaian per Domain', 'domainBar')">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase mb-6">Skor per Domain</h3>
            <div class="h-[300px]"><canvas id="domainBarChart"></canvas></div>
        </div>

        <div class="executive-card card-accent-purple p-8" onclick="openChartModal('Radar Analisis Aspek', 'radar')">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase mb-6">Radar Capaian Aspek</h3>
            <div class="h-[300px]"><canvas id="radarChart"></canvas></div>
        </div>
    </div>
</div>

<div id="chartModal" class="fixed inset-0 z-[100] hidden bg-gray-900/80 backdrop-blur-xl flex items-center justify-center p-6" onclick="closeChartModal()">
    <div class="bg-white dark:bg-gray-900 rounded-[3rem] w-full max-w-6xl h-[85vh] p-10 relative shadow-2xl transform transition-all scale-90 opacity-0" id="modalContainer" onclick="event.stopPropagation()">
        <button onclick="closeChartModal()" class="absolute -top-4 -right-4 h-12 w-12 flex items-center justify-center rounded-full bg-red-500 text-white shadow-xl hover:rotate-90 transition-all duration-300 text-xl font-bold">✕</button>
        <div class="mb-8 border-b dark:border-gray-800 pb-6">
            <h3 id="modalTitle" class="text-3xl font-black text-gray-900 dark:text-white text-center uppercase tracking-tighter"></h3>
        </div>
        <div class="h-[calc(100%-120px)]"><canvas id="modalChart"></canvas></div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartConfigs = {};
let modalChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';

    const radarLabels = @json($radarLabels);
    const radarRealisasi = @json($radarData).map(v => parseFloat(v) || 0);
    const radarTarget = @json($radarTarget).map(v => parseFloat(v) || 0);

   chartConfigs.radar = {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: [
                {
                    label: 'Realisasi Saat Ini (Tahun {{ $tahunMaster ?? $tahunDipilih ?? "N/A" }})',
                    data: radarRealisasi,
                    backgroundColor: 'rgba(30, 64, 175, 0.2)',
                    borderColor: '#1e40af',
                    pointBackgroundColor: '#1e40af',
                    borderWidth: 3,
                    z: 10
                },
                {
                    label: 'Target Rencana (Tahun {{ $tahunTerbaruDB ?? "2026" }})',
                    data: radarTarget,
                    borderColor: '#b45309', 
                    borderDash: [5, 5],
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#b45309',
                    z: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    min: 0,
                    max: 5,
                    beginAtZero: true,
                    ticks: { display: false, stepSize: 1 }
                }
            }
        }
    };

    chartConfigs.mixed = {
        type: 'line',
        data: {
            labels: @json($mixedLabels),
            datasets: [{
                label: 'Skor Indeks',
                data: @json($mixedValues).map(v => parseFloat(v)),
                borderColor: '#1e40af',
                borderWidth: 4,
                fill: true,
                backgroundColor: 'rgba(30, 64, 175, 0.05)',
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 3
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 5, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    };

    chartConfigs.domainBar = {
        type: 'bar',
        data: {
            labels: @json($tahunList),
            datasets: @json($lineChartDatasets).map((ds, i) => ({
                ...ds,
                backgroundColor: ['#1e40af', '#059669', '#b45309', '#6d28d9'][i % 4],
                borderRadius: 8
            }))
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 5 } }
        }
    };

    new Chart(document.getElementById('mixedChart'), chartConfigs.mixed);
    new Chart(document.getElementById('domainBarChart'), chartConfigs.domainBar);
    new Chart(document.getElementById('radarChart'), chartConfigs.radar);
});

function openChartModal(title, configKey) {
    const modal = document.getElementById('chartModal');
    const container = document.getElementById('modalContainer');
    document.getElementById('modalTitle').innerText = title;
    
    modal.classList.remove('hidden');
    setTimeout(() => { 
        container.classList.remove('scale-90', 'opacity-0'); 
        container.classList.add('scale-100', 'opacity-100'); 
    }, 50);
    
    if (modalChartInstance) modalChartInstance.destroy();
    
    const config = JSON.parse(JSON.stringify(chartConfigs[configKey]));
    modalChartInstance = new Chart(document.getElementById('modalChart'), config);
}

function closeChartModal() {
    const container = document.getElementById('modalContainer');
    container.classList.remove('scale-100', 'opacity-100'); 
    container.classList.add('scale-90', 'opacity-0');
    setTimeout(() => { 
        document.getElementById('chartModal').classList.add('hidden'); 
    }, 300);
}
</script>
@endsection