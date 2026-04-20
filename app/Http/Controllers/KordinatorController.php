<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Indikator; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HasilIndeks;
use App\Models\Domain;
use App\Models\Aspek;

class KordinatorController extends Controller
{
public function dashboard()
{
    $tahunTarget = DB::table('penilaian_kriteria')
        ->select('tahun')
        ->selectRaw("SUM(CASE WHEN status_target = 'verified' THEN 1 ELSE 0 END) as total_verified")
        ->selectRaw("COUNT(*) as total_data")
        ->whereNotNull('status_target') 
        ->where('status_target', '!=', '') 
        ->groupBy('tahun')
        ->get()
        ->map(function($item) {
            $item->is_completed = ($item->total_verified >= $item->total_data);
            return $item;
        });

    return view('kordinator.dashboard', compact('tahunTarget'));
}
public function kirimKeKoordinator(Request $request) 
{
    $tahun = $request->tahun;
    $affected = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->whereNotNull('nilai_target')
        ->where('nilai_target', '>', 0)
        ->update([
            'status_target' => 'final',
            'updated_at' => now()
        ]);
    
    if($affected > 0) {
        return response()->json(['message' => 'Target berhasil dikirim ke Koordinator.']);
    }
    
    return response()->json(['message' => 'Gagal mengirim, pastikan target sudah diisi.'], 400);
}
public function showTargetVerif($tahun)
{
    $penilaianRaw = DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->get();

    if($penilaianRaw->isEmpty()) {
        return redirect()->route('kordinator.dashboard')->with('error', 'Data tidak ditemukan.');
    }

    $draft = $penilaianRaw->groupBy('id_indikator')->map(function($group) {
        return $group->whereNotNull('nilai_target')->first()->nilai_target ?? 0;
    })->toArray();

    $isVerified = $penilaianRaw->where('status_target', 'verified')->count() > 0;
    $idIndikatorAktif = $penilaianRaw->pluck('id_indikator')->unique()->toArray();
    $domains = Domain::with(['aspek.indikator' => function($query) use ($idIndikatorAktif) {
            $query->whereIn('id_indikator', $idIndikatorAktif);
        }])
        ->whereHas('aspek.indikator', function($query) use ($idIndikatorAktif) {
            $query->whereIn('id_indikator', $idIndikatorAktif);
        })
        ->get();

    $availableYears = DB::table('penilaian_kriteria')->distinct()->pluck('tahun')->sortDesc();

    return view('kordinator.targetverif', compact('domains', 'draft', 'tahun', 'availableYears', 'isVerified'));
}

public function indexNilai(Request $request) {
    $tahunDipilih = $request->get('tahun', date('Y'));
    $tahunList = DB::table('hasil_indeks')
        ->distinct()
        ->pluck('tahun')
        ->sortDesc();
    if ($tahunList->isEmpty()) {
        $tahunList = collect([date('Y')]);
    }
    return view('kordinator.nilai', compact('tahunList', 'tahunDipilih'));
}

public function showChart(Request $request)
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

    return view('kordinator.chart', compact(
        'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
        'tahunFinalList', 'lineChartDatasets', 'radarLabels', 'radarData', 
        'radarTarget', 'indikatorLabels', 'doughnutData'
    ));
}


public function getDetailData($id, $tahun)
{
    $kriteria = DB::table('kriteria')
        ->leftJoin('penilaian_kriteria', function($join) use ($tahun) {
            $join->on('kriteria.id_kriteria', '=', 'penilaian_kriteria.id_kriteria')
                 ->where('penilaian_kriteria.tahun', '=', $tahun);
        })
        ->where('kriteria.id_indikator', $id)
        ->select('kriteria.id_kriteria', 'kriteria.nama_kriteria', 'penilaian_kriteria.nilai_target')
        ->orderBy('kriteria.id_kriteria', 'asc')
        ->get();

    $targetValue = $kriteria->where('nilai_target', '>', 0)->first()->nilai_target ?? 0;

    return response()->json([
        'status' => 'success',
        'detail' => DB::table('indikator')->where('id_indikator', $id)->first(),
        'kriteria' => $kriteria,
        'target_value' => (float)$targetValue,
        'mode' => 'view'
    ]);
}
public function approveTarget(Request $request)
{
    $tahun = $request->tahun;
    $action = $request->action;
    $user = Auth::user();

    try {
        DB::beginTransaction();

        if ($action === 'verify') {
            DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->update([
                    'status_target' => 'verified',
                    'updated_at' => now()
                ]);

            $indikatorIds = DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->distinct()
                ->pluck('id_indikator');

            foreach ($indikatorIds as $idInd) {
                $catatan = DB::table('catatan_kriteria')->where(['id_indikator' => $idInd, 'tahun' => $tahun])->first();
                
                DB::table('catatan_logs')->insert([
                    'id_catatan'   => $catatan->id_catatan ?? null,
                    'id_indikator' => $idInd,
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'role'         => 'kordinator',
                    'tahun'        => $tahun,
                    'aksi'         => 'Koordinator menyetujui usulan target nilai',
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Target berhasil disetujui dan dipublikasikan.'
            ]);

        } else if ($action === 'reject') {
            $indikatorIds = DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->distinct()
                ->pluck('id_indikator');
            DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->update([
                    'status_target' => null, 
                    'updated_at' => now()
                ]);

            foreach ($indikatorIds as $idInd) {
                $catatan = DB::table('catatan_kriteria')->where(['id_indikator' => $idInd, 'tahun' => $tahun])->first();

                DB::table('catatan_logs')->insert([
                    'id_catatan'   => $catatan->id_catatan ?? null,
                    'id_indikator' => $idInd,
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'role'         => 'kordinator',
                    'tahun'        => $tahun,
                    'aksi'         => 'Koordinator menolak usulan target (dikembalikan ke pengisian target)',
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data target telah dikembalikan ke pengisian target.',
                'redirect' => route('kordinator.dashboard')
            ]);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
}