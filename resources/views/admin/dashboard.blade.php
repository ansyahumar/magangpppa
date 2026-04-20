@extends('admin.layouts.app')
   @include('layouts.fav')
   <title>Dashboard</title>

@section('content')
<style>
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }

    .animate-fade-in-up { animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-scale-in { animation: scaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark .glass-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .chart-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .bg-gradient-header {
        background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
    }
</style>

<div class="min-h-screen bg-gradient-header px-4 py-8 max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 pb-2 border-b border-gray-100 dark:border-gray-800 animate-fade-in-up">
        <div>
            <h2 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                Dashboard <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-500">SPBE</span>
            </h2>
            <p class="mt-2 text-gray-500 dark:text-gray-400 font-medium">Monitoring capaian indeks sistem pemerintahan secara real-time.</p>
        </div>

        <form id="formTahun" method="GET" action="{{ route('admin.dashboard') }}" class="relative">
            <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1.5 ml-1">Periode Laporan</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-hover:scale-110">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <select name="tahun" onchange="this.form.submit()" 
                        class="block w-full md:w-64 pl-12 pr-10 py-3.5 text-sm font-bold border-none ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-2xl shadow-sm focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer appearance-none">
                    <option value="all" {{ (string)$tahunDipilih === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                    @foreach($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ (string)$tahunDipilih === (string)$tahun ? 'selected' : '' }}>Tahun Data {{ $tahun }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </form>
    </div>

    <div class="glass-card rounded-3xl p-8 chart-card animate-scale-in delay-1">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div id="mainIndicator" class="w-3 h-10 bg-blue-600 rounded-full shadow-[0_0_15px_rgba(37,99,235,0.4)]"></div>
                <div>
                    <h3 id="mainTitleText" class="text-xl font-extrabold text-gray-800 dark:text-gray-100">Nilai SPBE Keseluruhan</h3>
                    <p class="text-xs text-gray-400 font-medium">Visualisasi perbandingan indeks utama</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="bg-gray-100 dark:bg-gray-900/50 p-1.5 rounded-2xl flex items-center gap-1">
                    <select id="mainChartSelector" class="bg-white dark:bg-gray-800 border-none text-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 block w-full px-4 py-2 dark:text-white font-bold shadow-sm cursor-pointer">
                        <option value="mixed">Indeks Utama</option>
                        <option value="line">Nilai Domain</option>
                        <option value="bar">Nilai Aspek</option>
                    </select>
                    <button onclick="zoomMainChart()" class="p-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-blue-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="relative h-[420px]">
            <canvas id="mainCombinedChart"></canvas>
        </div>
    </div>

<div class="grid grid-cols-1 gap-8">
    <div class="glass-card rounded-3xl p-10 chart-card animate-scale-in delay-2" onclick="openChartModal('Radar Nilai Aspek', 'radar')">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <span class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-2xl">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /></svg>
                </span>
                <div>
                    <h3 class="font-bold text-2xl text-gray-800 dark:text-gray-100">Radar Nilai Aspek</h3>
                    <p class="text-xs text-gray-400 font-medium">Distribusi kematangan per aspek SPBE</p>
                </div>
            </div>
            <span class="text-[10px] font-black text-purple-500 uppercase tracking-widest bg-purple-50 dark:bg-purple-900/20 px-3 py-1 rounded-md">Distribution Analysis</span>
        </div>
        <div class="relative h-[550px] w-full mx-auto">
            <canvas id="radarChart"></canvas>
        </div>
    </div>
</div>

<div id="chartModal" class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300" onclick="closeChartModal()">
    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] w-full max-w-6xl h-[85vh] p-10 relative shadow-2xl transform transition-all scale-95 opacity-0 border border-white/10" 
         id="modalContainer" onclick="event.stopPropagation()">
        
        <button onclick="closeChartModal()" class="absolute top-6 right-6 h-12 w-12 flex items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-rose-500 hover:text-white transition-all group">
            <svg class="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <div class="flex flex-col h-full">
            <div class="mb-8">
                <h3 id="modalTitle" class="text-3xl font-black text-gray-800 dark:text-gray-100 text-center"></h3>
                <div class="w-20 h-1.5 bg-blue-600 mx-auto mt-4 rounded-full"></div>
            </div>
            <div class="flex-1 min-h-0">
                <canvas id="modalChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartConfigs = {};
let activeMainChartInstance = null;

function getRandomColor(index) {
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316', '#64748b', '#22c55e'];
    return colors[index % colors.length];
}

document.addEventListener('DOMContentLoaded', () => {
    const isRekap = "{{ $tahunDipilih }}" === "all";
    const rawBarDatasets = @json($barChartDatasets ?? []);
    const rawLineDatasets = @json($lineChartDatasets ?? []);

    chartConfigs.mixed = {
        title: 'Nilai SPBE Keseluruhan per Tahun',
        indicator: 'bg-blue-600',
        type: 'bar',
        data: {
            labels: @json($mixedLabels),
            datasets: [
                { type: 'line', label: 'Trend Indeks', data: @json($mixedValues), borderColor: '#2563eb', borderWidth: 4, tension: 0.4, fill: false },
                { type: 'bar', label: 'Nilai Indeks', data: @json($mixedValues), backgroundColor: 'rgba(59, 130, 246, 0.1)', borderRadius: 8 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 5 } } }
    };

    chartConfigs.line = {
        title: 'Nilai Domain SPBE',
        indicator: 'bg-emerald-500',
        type: isRekap ? 'line' : 'bar',
        data: {
            labels: @json($lineChartLabels ?? []),
            datasets: isRekap 
                ? rawLineDatasets.map((ds, i) => ({ ...ds, borderColor: getRandomColor(i), backgroundColor: getRandomColor(i), tension: 0.3 }))
                : [{
                    label: 'Nilai Domain',
                    data: rawLineDatasets.map(ds => (ds.data && ds.data.length > 0) ? ds.data[0] : 0),
                    backgroundColor: rawLineDatasets.map((_, i) => getRandomColor(i)),
                    borderRadius: 8
                }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: isRekap } },
            scales: { y: { beginAtZero: true, max: 5 } } 
        }
    };

    chartConfigs.bar = {
        title: 'Nilai Aspek SPBE',
        indicator: 'bg-amber-500',
        type: 'bar',
        data: {
            labels: @json($barChartLabels ?? []),
            datasets: isRekap 
                ? rawBarDatasets.map((ds, i) => ({ ...ds, backgroundColor: getRandomColor(i), borderRadius: 4 }))
                : [{
                    label: 'Nilai Aspek',
                    data: rawBarDatasets.map(ds => (ds.data && ds.data.length > 0) ? ds.data[0] : 0),
                    backgroundColor: rawBarDatasets.map((_, i) => getRandomColor(i)),
                    borderRadius: 6
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: isRekap } },
            scales: { 
                y: { beginAtZero: true, max: 5 },
                x: { ticks: { autoSkip: false, maxRotation: 45 } }
            }
        }
    };

chartConfigs.radar = {
    type: 'radar',
    data: {
        labels: @json($radarLabels ?? []),
        datasets: [
            { 
                label: 'Realisasi', 
                data: @json($radarData ?? []), 
                fill: true, 
                backgroundColor: 'rgba(59, 130, 246, 0.2)', 
                borderColor: 'rgb(59, 130, 246)', 
                borderWidth: 3,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(59, 130, 246)'
            },
            { 
                label: 'Target', 
                data: @json($radarTarget ?? []), 
                fill: false, 
                borderColor: '#ef4444', 
                borderDash: [5, 5], 
                borderWidth: 2,
                pointRadius: 0 
            }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        layout: {
            padding: 20 
        },
        scales: { 
            r: { 
                angleLines: { color: 'rgba(156, 163, 175, 0.2)' },
                grid: { color: 'rgba(156, 163, 175, 0.2)' },
                pointLabels: {
                    font: {
                        size: 12,
                        weight: '600'
                    },
                    color: '#64748b'
                },
                beginAtZero: true, 
                max: 5,
                ticks: {
                    stepSize: 1,
                    display: false
                }
            } 
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 30,
                    usePointStyle: true,
                    font: { size: 14, weight: '600' }
                }
            }
        }
    }
};

    new Chart(document.getElementById('radarChart'), chartConfigs.radar);
    window.updateMainChart = function(key) {
        const ctx = document.getElementById('mainCombinedChart').getContext('2d');
        const config = chartConfigs[key];
        document.getElementById('mainTitleText').innerText = config.title;
        document.getElementById('mainIndicator').className = `w-2 h-6 rounded-full transition-colors duration-300 ${config.indicator}`;
        if (activeMainChartInstance) activeMainChartInstance.destroy();
        activeMainChartInstance = new Chart(ctx, config);
    };

    document.getElementById('mainChartSelector').addEventListener('change', (e) => updateMainChart(e.target.value));
    updateMainChart('mixed');
});

let modalChartInstance = null;
function openChartModal(title, configKey) {
    const modal = document.getElementById('chartModal');
    const container = document.getElementById('modalContainer');
    document.getElementById('modalTitle').innerText = title;
    modal.classList.remove('hidden');
    setTimeout(() => { 
        container.classList.replace('scale-95', 'scale-100'); 
        container.classList.replace('opacity-0', 'opacity-100'); 
    }, 10);

    if (modalChartInstance) modalChartInstance.destroy();
    const config = JSON.parse(JSON.stringify(chartConfigs[configKey]));
    modalChartInstance = new Chart(document.getElementById('modalChart'), config);
}

function zoomMainChart() {
    const activeKey = document.getElementById('mainChartSelector').value;
    openChartModal(chartConfigs[activeKey].title, activeKey);
}

function closeChartModal() {
    const container = document.getElementById('modalContainer');
    container.classList.replace('scale-100', 'scale-95'); 
    container.classList.replace('opacity-100', 'opacity-0');
    setTimeout(() => { document.getElementById('chartModal').classList.add('hidden'); }, 300);
}
</script>
@endsection