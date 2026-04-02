<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Indikator;
use App\Models\Aspek;
use App\Models\Domain;
use App\Models\HasilIndeks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahunDipilih = $request->input('tahun', 'all');

        $tahunList = DB::table('penilaian_kriteria')
            ->where('status', 'final')
            ->whereNotNull('nilai_asesor_internal')
            ->where('nilai_asesor_internal', '>', 0)
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'asc')
            ->pluck('tahun')
            ->toArray();

        $lineChartLabels = [];
        $barChartLabels = [];
        $lineChartDatasets = [];
        $barChartDatasets = [];
        $radarLabels = [];
        $radarData = [];
        $radarTarget = [];
        $indikatorLabels = [];
        $doughnutData = [];
        $mixedLabels = collect([]);
        $mixedValues = collect([]);

        if (empty($tahunList)) {
            return view('admin.dashboard', compact(
                'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
                'lineChartLabels', 'lineChartDatasets', 'barChartDatasets', 'barChartLabels',
                'radarLabels', 'radarData', 'radarTarget', 'indikatorLabels', 'doughnutData'
            ));
        }

        $queryIndeks = HasilIndeks::orderBy('tahun');
        if ($tahunDipilih !== 'all') {
            $queryIndeks->where('tahun', $tahunDipilih);
        }
        $hasilIndeks = $queryIndeks->get();
        $mixedLabels = $hasilIndeks->pluck('tahun');
        $mixedValues = $hasilIndeks->pluck('indeks_spbe');

        $tahunMaster = ($tahunDipilih === 'all') ? max($tahunList) : $tahunDipilih;

        $aspekList = Aspek::where('tahun', $tahunMaster)->orderBy('urutan')->get();
        $indikators = Indikator::where('tahun', $tahunMaster)->orderBy('urutan')->get();

        if ($tahunDipilih === 'all') {
            $lineChartLabels = $tahunList;
            $barChartLabels = $tahunList;
        } else {
            $lineChartLabels = Domain::where('tahun', $tahunDipilih)->orderBy('urutan')->pluck('nama_domain')->toArray();
            $barChartLabels = Aspek::where('tahun', $tahunDipilih)->orderBy('urutan')->pluck('nama_aspek')->toArray();
        }

        $namaDomainUnik = Domain::distinct()->pluck('nama_domain');
        foreach ($namaDomainUnik as $nama) {
            $nilaiPerTahun = [];
            $hasValue = false;
            $loopTahun = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];

            foreach ($loopTahun as $th) {
                $nilai = DB::table('penilaian_kriteria')
                    ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                    ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                    ->where(['penilaian_kriteria.tahun' => $th, 'domain.nama_domain' => $nama, 'penilaian_kriteria.status' => 'final'])
                    ->avg('penilaian_kriteria.nilai_asesor_internal');

                if ($nilai > 0) $hasValue = true;
                $nilaiPerTahun[] = round($nilai ?? 0, 2);
            }

            if ($hasValue) {
                $lineChartDatasets[] = [
                    'label' => $nama,
                    'data' => $nilaiPerTahun,
                    'borderWidth' => 2,
                ];
            }
        }

        $namaAspekLoop = ($tahunDipilih === 'all') 
            ? Aspek::distinct()->pluck('nama_aspek') 
            : $barChartLabels;

        foreach ($namaAspekLoop as $nama) {
            $nilaiPerTahun = [];
            $hasValue = false;
            $loopTahun = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];

            foreach ($loopTahun as $th) {
                $nilaiAsesor = DB::table('penilaian_kriteria')
                    ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                    ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                    ->where(['penilaian_kriteria.tahun' => $th, 'aspek.nama_aspek' => $nama, 'penilaian_kriteria.status' => 'final'])
                    ->avg('penilaian_kriteria.nilai_asesor_internal');

                $dataAspek = DB::table('aspek')->where(['tahun' => $th, 'nama_aspek' => $nama])->first();
                
                $hasilAkhir = ($dataAspek && isset($dataAspek->aspek_verif) && !is_null($dataAspek->aspek_verif)) 
                    ? $dataAspek->aspek_verif 
                    : ($nilaiAsesor ?? 0);

                if ($hasilAkhir > 0) $hasValue = true;
                $nilaiPerTahun[] = round($hasilAkhir, 2);
            }

            if ($hasValue) {
                $barChartDatasets[] = [
                    'label' => $nama,
                    'data' => $nilaiPerTahun,
                ];
            }
        }

        $radarLabels = $aspekList->pluck('nama_aspek');
        foreach ($aspekList as $aspek) {
            $query = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->where('penilaian_kriteria.status', 'final')
                ->where('aspek.nama_aspek', $aspek->nama_aspek);

            if ($tahunDipilih !== 'all') {
                $query->where('penilaian_kriteria.tahun', $tahunDipilih);
            } else {
                $query->whereIn('penilaian_kriteria.tahun', $tahunList);
            }

            $nilaiRealisasi = $query->avg('penilaian_kriteria.nilai_asesor_internal');
            $radarData[] = round($nilaiRealisasi ?? 0, 2);
            $targetValue = ($tahunDipilih === 'all') ? DB::table('aspek')->where('nama_aspek', $aspek->nama_aspek)->orderBy('tahun', 'desc')->value('target') : $aspek->target;
            $radarTarget[] = (float)($targetValue ?? 0);
        }

        $namaIndikatorUnik = $indikators->pluck('nama_indikator')->unique();
        $indikatorLabels = $namaIndikatorUnik->values();
        foreach ($namaIndikatorUnik as $nama) {
            $query = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->where('penilaian_kriteria.status', 'final')
                ->where('indikator.nama_indikator', $nama);

            if ($tahunDipilih !== 'all') {
                $query->where('penilaian_kriteria.tahun', $tahunDipilih);
            }
            $nilai = $query->avg('penilaian_kriteria.nilai_asesor_internal');
            $doughnutData[] = round($nilai ?? 0, 2);
        }

        return view('admin.dashboard', compact(
            'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
            'lineChartLabels', 'lineChartDatasets', 'barChartDatasets', 'barChartLabels',
            'radarLabels', 'radarData', 'radarTarget', 'indikatorLabels', 'doughnutData',
        ));
    }
}