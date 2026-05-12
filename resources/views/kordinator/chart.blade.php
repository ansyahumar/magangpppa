@extends('layouts.kordinator')

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
        cursor: pointer;
    }

    .executive-card:hover {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }

    /* Warna Akses Dinamis */
    .accent-active { border-left: 6px solid {{ $modulAktif == 'pemdi' ? '#059669' : '#1e40af' }}; }
    .text-active { color: {{ $modulAktif == 'pemdi' ? '#059669' : '#1e40af' }}; }
    .bg-active { background-color: {{ $modulAktif == 'pemdi' ? '#059669' : '#1e40af' }}; }

    .formal-select {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        font-weight: 700;
        cursor: pointer;
        appearance: none;
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.5em;
    }
</style>

<div class="px-6 py-8 max-w-7xl mx-auto space-y-8 animate-in">
    
    <!-- HEADER & TAB NAVIGATION -->
    <div class="flex flex-col space-y-6 border-b border-gray-200 pb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Dashboard Monitoring <span class="text-active">{{ strtoupper($modulAktif) }}</span>
                </h2>
                <p class="text-slate-500 font-medium mt-1">Laporan Capaian Indeks {{ $modulAktif == 'pemdi' ? 'Pembangunan Digital' : 'Sistem Pemerintahan Berbasis Elektronik' }}</p>
            </div>
            
            <!-- Filter Tahun -->
            <form id="formFilter" method="GET" action="{{ route('kordinator.chart') }}" class="flex items-center gap-2">
                <input type="hidden" name="modul" value="{{ $modulAktif }}">
                <select name="tahun" onchange="this.form.submit()" class="formal-select dark:bg-gray-800 dark:border-gray-700 bg-white pr-10 text-active">
                    <option value="all" {{ (string)$tahunDipilih === 'all' ? 'selected' : '' }}>RINGKASAN MULTI-TAHUN</option>
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" {{ (string)$tahunDipilih === (string)$th ? 'selected' : '' }}>LAPORAN TAHUN {{ $th }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Tab Pemisah SPBE vs PEMDI -->
        <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-2xl w-fit border shadow-inner">
            <a href="{{ route('kordinator.chart', ['modul' => 'spbe', 'tahun' => $tahunDipilih]) }}" 
               class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all {{ $modulAktif == 'spbe' ? 'bg-white dark:bg-blue-600 text-blue-700 dark:text-white shadow-md' : 'text-slate-500 hover:text-slate-700' }}">
                <i class="fa-solid fa-server mr-2"></i> MODUL SPBE
            </a>
            <a href="{{ route('kordinator.chart', ['modul' => 'pemdi', 'tahun' => $tahunDipilih]) }}" 
               class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all {{ $modulAktif == 'pemdi' ? 'bg-white dark:bg-emerald-600 text-emerald-700 dark:text-white shadow-md' : 'text-slate-500 hover:text-slate-700' }}">
                <i class="fa-solid fa-microchip mr-2"></i> MODUL PEMDI
            </a>
        </div>
    </div>

    <!-- MAIN CHART -->
    <div class="executive-card accent-active p-8" onclick="openChartModal('Indeks {{ strtoupper($modulAktif) }}', 'mixed')">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 rounded-lg bg-opacity-10 {{ $modulAktif == 'pemdi' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wide">Tren Indeks {{ strtoupper($modulAktif) }}</h3>
        </div>
        <div class="h-[350px] relative">
            <canvas id="mixedChart"></canvas>
        </div>
    </div>

    <!-- SUB CHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="executive-card accent-active p-8" onclick="openChartModal('Skor per Domain', 'domainBar')">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase mb-6">Skor per Domain {{ strtoupper($modulAktif) }}</h3>
            <div class="h-[300px] relative">
                <canvas id="domainBarChart"></canvas>
            </div>
        </div>

        <div class="executive-card accent-active p-8" onclick="openChartModal('Radar Capaian Aspek', 'radar')">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white uppercase mb-6">Radar Capaian Aspek</h3>
            <div class="h-[300px] relative">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartConfigs = {};
let modalChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';

    const toArray = (data) => {
        if (!data) return [];
        const res = Array.isArray(data) ? data : Object.values(data);
        return res.map(v => parseFloat(v) || 0);
    };

    const radarLabels = @json($radarLabels ?? []);
    const radarRealisasi = toArray(@json($radarData ?? []));
    const radarTarget = toArray(@json($radarTarget ?? []));

    chartConfigs.radar = {
        type: 'radar',
        data: {
            labels: radarLabels,
            datasets: [
                {
                    label: 'Realisasi Saat Ini',
                    data: radarRealisasi,
                    backgroundColor: 'rgba(30, 64, 175, 0.2)',
                    borderColor: '#1e40af',
                    pointBackgroundColor: '#1e40af',
                    borderWidth: 3,
                    z: 10
                },
                {
                    label: 'Target Rencana',
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
                r: { min: 0, max: 5, beginAtZero: true, ticks: { display: false, stepSize: 1 } }
            }
        }
    };

    const mixedLabels = toArray(@json($mixedLabels ?? []));
    const mixedValues = toArray(@json($mixedValues ?? []));

    chartConfigs.mixed = {
        type: 'line',
        data: {
            labels: mixedLabels,
            datasets: [{
                label: 'Skor Indeks',
                data: mixedValues,
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

    const domainDatasets = @json($lineChartDatasets ?? []);
    chartConfigs.domainBar = {
        type: 'bar',
        data: {
            labels: toArray(@json($tahunList ?? [])),
            datasets: domainDatasets.map((ds, i) => ({
                label: ds.label,
                data: toArray(ds.data),
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
    setTimeout(() => { document.getElementById('chartModal').classList.add('hidden'); }, 300);
}
</script>
@endsection