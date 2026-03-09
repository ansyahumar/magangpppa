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
    $tahunList = HasilIndeks::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
    $tahunDipilih = $request->input('tahun', (count($tahunList) > 0 ? $tahunList[0] : date('Y')));
    
    $hasilIndeks = HasilIndeks::orderBy('tahun')->get();
    $mixedLabels = $hasilIndeks->pluck('tahun');
    $mixedValues = $hasilIndeks->pluck('indeks_spbe');

    
    $tahunMaster = ($tahunDipilih === 'all') ? (count($tahunList) > 0 ? max($tahunList) : date('Y')) : $tahunDipilih;

    $domainList = Domain::where('tahun', $tahunMaster)->orderBy('urutan')->get();
    $aspekList = Aspek::where('tahun', $tahunMaster)->orderBy('urutan')->get();
    $indikators = Indikator::where('tahun', $tahunMaster)->orderBy('urutan')->get();

    
    $lineChartDatasets = [];    $tahunFinalList = ($tahunDipilih === 'all') ? $mixedLabels->toArray() : [$tahunDipilih];
    $namaDomainUnik = Domain::distinct()->pluck('nama_domain')->unique();

    foreach ($namaDomainUnik as $nama) {
        $nilaiPerTahun = [];
        $hasValue = false;
        foreach ($tahunFinalList as $th) {
            $nilai = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
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

    $radarLabels = $aspekList->pluck('nama_aspek');
    $radarData = [];
    $radarTarget = [];

    foreach ($aspekList as $aspek) {
        $val = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->where('aspek.nama_aspek', $aspek->nama_aspek)
            ->where('penilaian_kriteria.status', 'final');
        
        if ($tahunDipilih !== 'all') $val->where('penilaian_kriteria.tahun', $tahunDipilih);
        
        $radarData[] = round($val->avg('nilai_asesor_internal') ?? 0, 2);

        if ($tahunDipilih === 'all') {
            $targetVal = DB::table('aspek')->where('nama_aspek', $aspek->nama_aspek)->orderBy('tahun', 'desc')->value('target');
        } else {
            $targetVal = $aspek->target;
        }
        $radarTarget[] = (float)($targetVal ?? 0);
    }

    $indikatorLabels = $indikators->pluck('nama_indikator');
    $doughnutData = [];
    foreach ($indikators as $ind) {
        $valInd = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->where('indikator.nama_indikator', $ind->nama_indikator)
            ->where('penilaian_kriteria.status', 'final');
        if ($tahunDipilih !== 'all') $valInd->where('penilaian_kriteria.tahun', $tahunDipilih);
        $doughnutData[] = round($valInd->avg('nilai_asesor_internal') ?? 0, 2);
    }

    return view('p1.chart', compact(
        'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
        'tahunFinalList', 'lineChartDatasets', 'radarLabels', 'radarData', 
        'radarTarget', 'indikatorLabels', 'doughnutData'
    ));
}
 public function lihatNilai(Request $request)
    {
        $tahunTerbaru = DB::table('hasil_indeks')->orderBy('tahun', 'desc')->value('tahun');
        $tahunDipilih = $request->input('tahun', $tahunTerbaru ?? date('Y'));
        $tahunLalu = (int)$tahunDipilih - 1;

        $allDomainsList = DB::table('domain')->where('tahun', $tahunDipilih)->orderBy('urutan', 'asc')->get();
        $allAspeks = DB::table('aspek')->where('tahun', $tahunDipilih)->orderBy('urutan', 'asc')->get()->groupBy('id_domain');

        $domainData = DB::table('domain_hasil')->where('tahun', $tahunDipilih)->get()->keyBy('id_domain');
        $aspekData = DB::table('aspek_hasil')->where('tahun', $tahunDipilih)->get()->keyBy('id_aspek');
        $indeksSekarang = DB::table('hasil_indeks')->where('tahun', $tahunDipilih)->first();

        $domainDataLalu = DB::table('domain_hasil')
            ->join('domain', 'domain_hasil.id_domain', '=', 'domain.id_domain')
            ->where('domain_hasil.tahun', $tahunLalu)
            ->select('domain_hasil.*', 'domain.nama_domain')
            ->get()->keyBy('nama_domain');

        $aspekDataLalu = DB::table('aspek_hasil')
            ->join('aspek', 'aspek_hasil.id_aspek', '=', 'aspek.id_aspek')
            ->where('aspek_hasil.tahun', $tahunLalu)
            ->select('aspek_hasil.*', 'aspek.nama_aspek')
            ->get()->keyBy('nama_aspek');

        $indeksLalu = DB::table('hasil_indeks')->where('tahun', $tahunLalu)->first();
        $tahunList = DB::table('hasil_indeks')->orderBy('tahun', 'desc')->pluck('tahun');

        return view('p1.nilai', compact(
            'tahunDipilih', 'tahunLalu', 'tahunList', 
            'allDomainsList', 'allAspeks', 
            'domainData', 'domainDataLalu', 
            'aspekData', 'aspekDataLalu',
            'indeksSekarang', 'indeksLalu'
        ));
    }
}