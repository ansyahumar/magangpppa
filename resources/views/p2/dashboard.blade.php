@extends('layouts.p2')

<link rel="icon" type="image/x-icon" href="https://siga.kemenpppa.go.id/themes/sigabn/assets/images/favicon.ico">
<title>Dashboard P2 SPBE</title>

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    
    .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    .animate-scale-in { animation: scaleIn 0.5s ease-out forwards; opacity: 0; }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    .chart-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    #chartModal { backdrop-filter: blur(8px); }
</style>

<div class="px-4 py-6 max-w-7xl mx-auto">

     <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 animate-fade-in-up">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Dashboard <span class="text-blue-600">P2 SPBE</span>
            </h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm italic">Visualisasi data capaian berkelanjutan seluruh periode.</p>
        </div>

        <form id="formTahun" method="GET" action="{{ url()->current() }}" class="relative group">
            <div class="relative flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <select name="tahun" onchange="this.form.submit()" 
                        class="block w-full md:w-64 pl-11 pr-10 py-3 text-sm font-bold border-none ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-2xl shadow-sm focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer transition-all">
                    <option value="all" {{ (string)$tahunDipilih === 'all' ? 'selected' : '' }}>Semua Tahun (Trend)</option>
                    @foreach($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ (string)$tahunDipilih === (string)$tahun ? 'selected' : '' }}>Periode Data {{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-8 chart-card animate-scale-in delay-1" 
         onclick="openChartModal('Nilai SPBE Keseluruhan', 'mixed')">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                Trend Indeks SPBE Nasional
            </h3>
            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-widest">Global Score</span>
        </div>
        <div class="relative h-[300px]">
            <canvas id="mixedChart"></canvas>
        </div>
    </div>

     <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 chart-card animate-scale-in delay-2"
             onclick="openChartModal('Nilai Domain SPBE', 'line')">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 text-sm uppercase">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span> Capaian per Domain
            </h3>
            <div class="relative h-[300px]"><canvas id="lineChart"></canvas></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 chart-card animate-scale-in delay-2"
             onclick="openChartModal('Nilai Aspek SPBE', 'bar')">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 text-sm uppercase">
                <span class="w-2 h-6 bg-amber-500 rounded-full"></span> Capaian per Aspek
            </h3>
            <div class="relative h-[300px]"><canvas id="barChart"></canvas></div>
        </div>
    </div>

     <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 chart-card animate-scale-in delay-3"
             onclick="openChartModal('Radar Analisis Capaian', 'radar')">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 text-sm uppercase">
                <span class="w-2 h-6 bg-purple-500 rounded-full"></span> Realisasi vs Target
            </h3>
            <div class="relative h-[380px]"><canvas id="radarChart"></canvas></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 chart-card animate-scale-in delay-3"
             onclick="openChartModal('Distribusi Nilai Indikator', 'doughnut')">
            <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 text-sm uppercase">
                <span class="w-2 h-6 bg-pink-500 rounded-full"></span> Proporsi Indikator
            </h3>
            <div class="relative h-[380px]"><canvas id="doughnutChart"></canvas></div>
        </div>
    </div>
</div>

<div id="chartModal" class="fixed inset-0 z-[100] hidden bg-gray-900/80 flex items-center justify-center p-4 transition-all duration-300" onclick="closeChartModal()">
    <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-6xl h-[85vh] p-8 relative shadow-2xl transform transition-all scale-95 opacity-0" 
         id="modalContainer" onclick="event.stopPropagation()">
        <button onclick="closeChartModal()" class="absolute top-5 right-5 h-12 w-12 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-red-500 hover:text-white transition-all text-3xl font-light">
            &times;
        </button>
        <h3 id="modalTitle" class="text-2xl font-black text-gray-800 dark:text-gray-100 mb-8 text-center uppercase tracking-tighter"></h3>
        <div class="h-[calc(100%-100px)]">
            <canvas id="modalChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartConfigs = {};
const labelsTahun = @json($tahunList); 
function getRandomColor(index) {
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316', '#64748b', '#22c55e'];
    return colors[index % colors.length];
}

document.addEventListener('DOMContentLoaded', () => {
 
    chartConfigs.mixed = {
        type: 'line',
        data: {
            labels: @json($mixedLabels),
            datasets: [
                { label: 'Indeks SPBE', data: @json($mixedValues), borderColor: '#2563eb', borderWidth: 4, tension: 0.4, fill: true, backgroundColor: 'rgba(37, 99, 235, 0.05)', pointRadius: 6, pointHoverRadius: 8 },
                { type: 'bar', label: 'Capaian Tahunan', data: @json($mixedValues), backgroundColor: 'rgba(59, 130, 246, 0.1)', borderRadius: 10, barThickness: 40 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 5 } } }
    };

    chartConfigs.line = {
        type: 'line',
        data: {
            labels: labelsTahun,
            datasets: @json($lineChartDatasets).map((ds, i) => ({ 
                ...ds, 
                borderColor: getRandomColor(i), 
                backgroundColor: getRandomColor(i),
                tension: 0.3,
                spanGaps: true, 
                pointRadius: 5,
                pointHoverRadius: 7
            }))
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: { y: { beginAtZero: true, max: 5, ticks: { stepSize: 1 } } } 
        }
    };

    chartConfigs.bar = {
        type: 'bar',
        data: {
            labels: labelsTahun,
            datasets: @json($barChartDatasets).map((ds, i) => ({ ...ds, backgroundColor: getRandomColor(i), borderRadius: 8 }))
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 5 } } }
    };

    chartConfigs.radar = {
        type: 'radar',
        data: {
            labels: @json($radarLabels ?? []),
            datasets: [
                { label: 'Realisasi', data: @json($radarData ?? []), fill: true, backgroundColor: 'rgba(59, 130, 246, 0.2)', borderColor: 'rgb(59, 130, 246)', pointBackgroundColor: 'rgb(59, 130, 246)' },
                { label: 'Target', data: @json($radarTarget ?? []), fill: false, borderColor: '#ef4444', borderDash: [5, 5], pointBackgroundColor: '#ef4444' }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            scales: { r: { suggestedMin: 0, suggestedMax: 5, ticks: { stepSize: 1 } } },
            plugins: { legend: { position: 'bottom' } }
        }
    };

    const dDataRaw = @json($doughnutData);
    chartConfigs.doughnut = {
        type: 'doughnut',
        data: {
            labels: @json($indikatorLabels),
            datasets: [{ data: dDataRaw, backgroundColor: dDataRaw.map((_, i) => getRandomColor(i)), hoverOffset: 25, borderWidth: 0 }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '75%', 
            plugins: { legend: { display: false } } 
        }
    };

    new Chart(document.getElementById('mixedChart'), chartConfigs.mixed);
    new Chart(document.getElementById('lineChart'), chartConfigs.line);
    new Chart(document.getElementById('barChart'), chartConfigs.bar);
    new Chart(document.getElementById('radarChart'), chartConfigs.radar);
    new Chart(document.getElementById('doughnutChart'), chartConfigs.doughnut);
});

let modalChartInstance = null;

function openChartModal(title, configKey) {
    const modal = document.getElementById('chartModal');
    const container = document.getElementById('modalContainer');
    document.getElementById('modalTitle').innerText = title;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
    }, 10);

    if (modalChartInstance) modalChartInstance.destroy();
    
    const config = JSON.parse(JSON.stringify(chartConfigs[configKey]));
    modalChartInstance = new Chart(document.getElementById('modalChart'), config);
}

function closeChartModal() {
    const container = document.getElementById('modalContainer');
    container.classList.remove('scale-100', 'opacity-100');
    container.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        document.getElementById('chartModal').classList.add('hidden');
    }, 300);
}
</script>
@endsection