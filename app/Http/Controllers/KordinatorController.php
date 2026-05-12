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
// Tambahkan Request $request di sini
public function dashboard(Request $request) 
{
    // Sekarang variabel $request sudah tersedia dan bisa digunakan
    $modulAktif = $request->get('modul', 'spbe'); 

    $tahunTarget = DB::table('penilaian_kriteria')
        ->select('tahun')
        ->selectRaw("SUM(CASE WHEN status_target = 'verified' THEN 1 ELSE 0 END) as total_verified")
        ->selectRaw("COUNT(*) as total_data")
        ->where('modul', $modulAktif)
        ->whereNotNull('status_target') 
        ->where('status_target', '!=', '') 
        ->groupBy('tahun')
        ->get()
        ->map(function($item) {
            $item->is_completed = ($item->total_verified >= $item->total_data);
            return $item;
        });

    return view('kordinator.dashboard', compact('tahunTarget', 'modulAktif'));
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
public function showTargetVerif($tahun, Request $request)
{
    // Tangkap variabel modul dari URL, defaultnya 'spbe' jika tidak ada
    $modul = $request->get('modul', 'spbe');

    $penilaianRaw = DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->where('modul', $modul) // Sekarang variabel $modul sudah terdefinisi
                ->get();

    if($penilaianRaw->isEmpty()) {
        // Tambahkan query modul di redirect agar tidak kehilangan konteks
        return redirect()->route('kordinator.dashboard', ['modul' => $modul])
                         ->with('error', 'Data tidak ditemukan.');
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

    // Pastikan mengirim variabel 'modul' ke view agar UI bisa menyesuaikan warna/label
    return view('kordinator.targetverif', compact('domains', 'draft', 'tahun', 'availableYears', 'isVerified', 'modul'));
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
    // 1. Inisialisasi Parameter Dasar & Modul
    $modulAktif = $request->get('modul', 'spbe');
    
    // 2. Ambil List Tahun & Tahun Terpilih
    // Kita ambil tahun dari HasilIndeks yang sesuai dengan modul agar dropdown relevan
    $tahunList = HasilIndeks::where('modul', $modulAktif)
                            ->orderBy('tahun', 'desc')
                            ->pluck('tahun')
                            ->toArray();
                            
    $tahunDipilih = $request->input('tahun', (count($tahunList) > 0 ? $tahunList[0] : date('Y')));
    
    // 3. Ambil Data Tren (Grafik Garis Utama)
    $hasilIndeks = HasilIndeks::where('modul', $modulAktif)->orderBy('tahun')->get();
    $mixedLabels = $hasilIndeks->pluck('tahun');
    $mixedValues = $hasilIndeks->pluck('indeks_spbe'); 
    
    // 4. Tentukan Tahun Master & Tahun Final untuk Logic Grafik
    $tahunMaster = ($tahunDipilih === 'all') ? (count($tahunList) > 0 ? max($tahunList) : date('Y')) : $tahunDipilih;
    $tahunFinalList = ($tahunDipilih === 'all') ? $mixedLabels->toArray() : [$tahunDipilih];

    // 5. Query Master Data dengan JOIN ke Domain (Filter Modul Tanpa Ubah Database)
    $domainList = Domain::where('tahun', $tahunMaster)
                        ->where('modul', $modulAktif)
                        ->orderBy('urutan')
                        ->get();

    $aspekList = Aspek::join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('aspek.tahun', $tahunMaster)
                        ->where('domain.modul', $modulAktif)
                        ->select('aspek.*')
                        ->orderBy('aspek.urutan')
                        ->get();

    $indikators = Indikator::join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                        ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                        ->where('indikator.tahun', $tahunMaster)
                        ->where('domain.modul', $modulAktif)
                        ->select('indikator.*')
                        ->orderBy('indikator.urutan')
                        ->get();

    // 6. Inisialisasi Penampung Dataset Grafik (PENTING: Agar tidak Undefined)
    $lineChartDatasets = [];
    $namaDomainUnik = Domain::where('modul', $modulAktif)->distinct()->pluck('nama_domain');

    // 7. Logic Looping untuk Line Chart (Tren per Domain)
    foreach ($namaDomainUnik as $nama) {
        $nilaiPerTahun = [];
        $hasValue = false;

        foreach ($tahunFinalList as $th) {
            $nilai = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                ->where('domain.nama_domain', $nama)
                ->where('domain.modul', $modulAktif)
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

    // 8. Logic Radar Chart (Aspek)
    $radarLabels = $aspekList->pluck('nama_aspek');
    $radarData = [];
    $radarTarget = [];

    foreach ($aspekList as $aspek) {
        $val = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->where('aspek.nama_aspek', $aspek->nama_aspek)
            ->where('penilaian_kriteria.status', 'final');
        
        if ($tahunDipilih !== 'all') {
            $val->where('penilaian_kriteria.tahun', $tahunDipilih);
        }
        
        $radarData[] = round($val->avg('nilai_asesor_internal') ?? 0, 2);

        // Ambil Target
        if ($tahunDipilih === 'all') {
            $targetVal = DB::table('aspek')->where('nama_aspek', $aspek->nama_aspek)->orderBy('tahun', 'desc')->value('target');
        } else {
            $targetVal = $aspek->target;
        }
        $radarTarget[] = (float)($targetVal ?? 0);
    }

    // 9. Logic Doughnut Chart (Indikator)
    $indikatorLabels = $indikators->pluck('nama_indikator');
    $doughnutData = [];

    foreach ($indikators as $ind) {
        $valInd = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->where('indikator.nama_indikator', $ind->nama_indikator)
            ->where('penilaian_kriteria.status', 'final');

        if ($tahunDipilih !== 'all') {
            $valInd->where('penilaian_kriteria.tahun', $tahunDipilih);
        }

        $doughnutData[] = round($valInd->avg('nilai_asesor_internal') ?? 0, 2);
    }

    // 10. Kirim Data ke View
    return view('kordinator.chart', compact(
        'modulAktif',
        'tahunDipilih', 
        'tahunList', 
        'mixedLabels', 
        'mixedValues',
        'tahunFinalList', 
        'lineChartDatasets', 
        'radarLabels', 
        'radarData', 
        'radarTarget', 
        'indikatorLabels', 
        'doughnutData'
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
    $modul = $request->modul; // Ambil parameter modul dari request
    $action = $request->action;
    $user = Auth::user();

    try {
        DB::beginTransaction();

        // 1. Ambil jatah ID Indikator yang hanya milik MODUL & TAHUN ini
        // Ini kunci agar tidak menyentuh modul lain
        $myIndikatorIds = DB::table('indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('domain.modul', $modul)
            ->where('domain.tahun', $tahun)
            ->pluck('indikator.id_indikator');

        if ($myIndikatorIds->isEmpty()) {
            throw new \Exception("Tidak ada data indikator ditemukan untuk modul $modul tahun $tahun.");
        }

        if ($action === 'verify') {
            // Update status_target HANYA untuk jatah indikator di modul ini
            DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->whereIn('id_indikator', $myIndikatorIds)
                ->update([
                    'status_target' => 'verified',
                    'updated_at' => now()
                ]);

            // Catat Logs
            foreach ($myIndikatorIds as $idInd) {
                $catatan = DB::table('catatan_kriteria')
                    ->where(['id_indikator' => $idInd, 'tahun' => $tahun])
                    ->first();
                
                DB::table('catatan_logs')->insert([
                    'id_catatan'   => $catatan->id_catatan ?? null,
                    'id_indikator' => $idInd,
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'role'         => 'kordinator',
                    'tahun'        => $tahun,
                    'aksi'         => "Koordinator menyetujui usulan target nilai (Modul: " . strtoupper($modul) . ")",
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Target " . strtoupper($modul) . " berhasil disetujui dan dipublikasikan."
            ]);

        } else if ($action === 'reject') {
            // Kosongkan status_target HANYA untuk modul ini
            DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->whereIn('id_indikator', $myIndikatorIds)
                ->update([
                    'status_target' => null, 
                    'updated_at' => now()
                ]);

            foreach ($myIndikatorIds as $idInd) {
                $catatan = DB::table('catatan_kriteria')
                    ->where(['id_indikator' => $idInd, 'tahun' => $tahun])
                    ->first();

                DB::table('catatan_logs')->insert([
                    'id_catatan'   => $catatan->id_catatan ?? null,
                    'id_indikator' => $idInd,
                    'user_id'      => $user->id,
                    'name'         => $user->name,
                    'role'         => 'kordinator',
                    'tahun'        => $tahun,
                    'aksi'         => "Koordinator menolak usulan target (Modul: " . strtoupper($modul) . ")",
                    'created_at'   => now()
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Data target " . strtoupper($modul) . " telah dikembalikan.",
                'redirect' => route('kordinator.dashboard')
            ]);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
}