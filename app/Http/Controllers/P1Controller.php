<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HasilIndeks;
use App\Models\Domain;
use App\Models\Aspek;
use App\Models\Indikator;

class P1Controller extends Controller
{

public function lihatChart(Request $request)
{
    // 1. Tangkap parameter modul (default spbe)
    $modul = $request->input('modul', 'spbe');
    
    // Filter tahunList berdasarkan modul
    $tahunList = HasilIndeks::where('modul', $modul)->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
    $tahunDipilih = $request->input('tahun', (count($tahunList) > 0 ? $tahunList[0] : date('Y')));
    
    // Query Indeks (Filter Modul)
    $hasilIndeks = HasilIndeks::where('modul', $modul)->orderBy('tahun')->get();
    $mixedLabels = $hasilIndeks->pluck('tahun');
    $mixedValues = $hasilIndeks->pluck('indeks_spbe');

    $tahunMaster = ($tahunDipilih === 'all') ? (count($tahunList) > 0 ? max($tahunList) : date('Y')) : $tahunDipilih;

    // Filter Master Data berdasarkan Modul & Tahun
    $domainList = Domain::where('modul', $modul)->where('tahun', $tahunMaster)->orderBy('urutan')->get();
    $aspekList = Aspek::whereHas('domain', function($q) use ($modul) {
        $q->where('modul', $modul);
    })->where('tahun', $tahunMaster)->orderBy('urutan')->get();

    $lineChartDatasets = [];
    $tahunFinalList = ($tahunDipilih === 'all') ? $mixedLabels->toArray() : [$tahunDipilih];
    
    // Ambil nama domain unik khusus modul ini
    $namaDomainUnik = Domain::where('modul', $modul)->distinct()->pluck('nama_domain')->unique();

    foreach ($namaDomainUnik as $nama) {
        $nilaiPerTahun = [];
        $hasValue = false;
        foreach ($tahunFinalList as $th) {
            $nilai = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                ->where('domain.modul', $modul) // Filter Modul
                ->where('domain.nama_domain', $nama)
                ->where('penilaian_kriteria.tahun', $th)
                ->where('penilaian_kriteria.status', 'final')
                ->avg('nilai_asesor_internal');

            if ($nilai > 0) $hasValue = true;
            $nilaiPerTahun[] = round($nilai ?? 0, 2);
        }
        if ($hasValue) {
            $lineChartDatasets[] = [
                'label' => $nama,
                'data' => $nilaiPerTahun,
            ];
        }
    }

    // Radar Data (Filter Modul)
    $radarLabels = $aspekList->pluck('nama_aspek');
    $radarData = [];
    $radarTarget = [];

    foreach ($aspekList as $aspek) {
        $val = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('domain.modul', $modul) // Filter Modul
            ->where('aspek.nama_aspek', $aspek->nama_aspek)
            ->where('penilaian_kriteria.status', 'final');
        
        if ($tahunDipilih !== 'all') $val->where('penilaian_kriteria.tahun', $tahunDipilih);
        
        $radarData[] = round($val->avg('nilai_asesor_internal') ?? 0, 2);
        $radarTarget[] = (float)($aspek->target ?? 0);
    }

    return view('p1.chart', compact(
        'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
        'tahunFinalList', 'lineChartDatasets', 'radarLabels', 'radarData', 
        'radarTarget', 'modul'
    ));
}
public function lihatNilai(Request $request)
{
    $modul = $request->input('modul', 'spbe');
    
    $tahunTerbaru = DB::table('hasil_indeks')->where('modul', $modul)->orderBy('tahun', 'desc')->value('tahun');
    $tahunDipilih = $request->input('tahun', $tahunTerbaru ?? date('Y'));
    $tahunLalu = (int)$tahunDipilih - 1;

    // Filter Domain & Aspek berdasarkan Modul
    $allDomainsList = DB::table('domain')
                        ->where('modul', $modul)
                        ->where('tahun', $tahunDipilih)
                        ->orderBy('urutan', 'asc')
                        ->get();

    $allAspeks = DB::table('aspek')
                    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                    ->where('domain.modul', $modul)
                    ->where('aspek.tahun', $tahunDipilih)
                    ->select('aspek.*')
                    ->orderBy('aspek.urutan', 'asc')
                    ->get()
                    ->groupBy('id_domain');

    // Data Hasil (Filter Modul)
    $domainData = DB::table('domain_hasil')
                    ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
                    ->where('domain.modul', $modul)
                    ->where('domain_hasil.tahun', $tahunDipilih)
                    ->select('domain_hasil.*')
                    ->get()->keyBy('id_domain');

    $aspekData = DB::table('aspek_hasil')
                    ->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
                    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                    ->where('domain.modul', $modul)
                    ->where('aspek_hasil.tahun', $tahunDipilih)
                    ->select('aspek_hasil.*')
                    ->get()->keyBy('id_aspek');

    $indeksSekarang = DB::table('hasil_indeks')
                        ->where('modul', $modul)
                        ->where('tahun', $tahunDipilih)
                        ->first();

    // Data Tahun Lalu (Filter Modul)
    $domainDataLalu = DB::table('domain_hasil')
        ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
        ->where('domain.modul', $modul)
        ->where('domain_hasil.tahun', $tahunLalu)
        ->select('domain_hasil.*', 'domain.nama_domain')
        ->get()->keyBy('nama_domain');

    $indeksLalu = DB::table('hasil_indeks')
                    ->where('modul', $modul)
                    ->where('tahun', $tahunLalu)
                    ->first();

    $tahunList = DB::table('hasil_indeks')->where('modul', $modul)->orderBy('tahun', 'desc')->pluck('tahun');

    return view('p1.nilai', compact(
        'tahunDipilih', 'tahunLalu', 'tahunList', 
        'allDomainsList', 'allAspeks', 
        'domainData', 'domainDataLalu', 
        'aspekData', 'indeksSekarang', 'indeksLalu', 'modul'
    ));
}
}