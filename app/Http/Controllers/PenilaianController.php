<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PenilaianHelper;
use App\Models\PenilaianIndikator;
use App\Models\HasilIndeks;
use App\Models\Domain;
use App\Models\Indikator;
use App\Models\Aspek;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{

public function form(Request $request)
{
   
$modul = $request->input('modul', 'spbe');
    $tahun = $request->input('tahun', date('Y'));

     $availableYears = Domain::where('modul', $modul)
        ->distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');
    

    // 1. Ambil ID Indikator yang masuk dalam modul & tahun ini (sebagai filter tambahan)
    $indikatorIds = DB::table('indikator')
    ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
    ->where('domain.modul', $modul)
    ->where('domain.tahun', $tahun)
    ->pluck('indikator.id_indikator')
    ->toArray();

    // 2. Cek apakah ada record yang berstatus 'final' di tabel penilaian_indikator
    // Pastikan filter user_id ada jika setiap unit kerja mengisi sendiri-sendiri
    $isFinal = DB::table('penilaian_indikator')
        ->whereIn('id_indikator', $indikatorIds)
        ->where('tahun', $tahun)
        ->where('modul', $modul)
        ->where('status', 'final')
        ->exists();

        $statusKunciFinal = $isFinal;

$locked = HasilIndeks::where('tahun', $tahun)
        ->where('modul', $modul)
        ->exists();

    // DEBUGGING (Hapus jika sudah jalan)
    // dd($isFinal);
    $draft = PenilaianIndikator::where('tahun', $tahun)
        ->get()
        ->keyBy('id_indikator');
    
    $domains = Domain::where('tahun', $tahun)
        ->where('modul', $modul)
        ->with(['aspek' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)
              ->orderBy('urutan');
        }, 'aspek.indikator' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)
              ->orderBy('urutan');
        }])
        ->orderBy('urutan')
        ->get();

   return view('penilaian.form', [
    'domains'            => $domains,
    'tahun'              => $tahun,
    'modul'              => $modul,
    'locked'             => $locked,
    'draft'              => $draft,
    'availableYears'     => $availableYears,
    'statusKunciFinal'   => $statusKunciFinal, // Nama baru yang unik
]);
}

public function process(Request $request)
{
    $modul = $request->input('modul');
    $tahun = $request->input('tahun', date('Y'));

    try {
        DB::beginTransaction();

        // 1. Ambil ID indikator milik modul dan tahun ini
        $targetIndikatorIds = DB::table('indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('domain.modul', $modul)
            ->where('domain.tahun', $tahun)
            ->pluck('indikator.id_indikator');

        if ($targetIndikatorIds->isEmpty()) {
            throw new \Exception("Tidak ada data indikator untuk modul $modul di tahun $tahun.");
        }

        // 2. Paksa update status menjadi 'final' agar tombol terkunci
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->whereIn('id_indikator', $targetIndikatorIds)
            ->where('modul', $modul)
            ->update(['status' => 'final']);

        DB::table('penilaian_indikator')
            ->where('tahun', $tahun)
            ->whereIn('id_indikator', $targetIndikatorIds)
            ->where('modul', $modul)
            ->update(['status' => 'final']);

        // 3. Jalankan Helper Perhitungan
        // Fungsi ini akan menghitung nilai akhir berdasarkan data yang baru saja di-set 'final'
        PenilaianHelper::calculateIndices($tahun, $modul);
        
        DB::commit();
        
        return response()->json([
            'message' => 'Lengkap! Seluruh penilaian ' . strtoupper($modul) . ' tahun ' . $tahun . ' telah dihitung dan difinalisasi.'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}
public function hasilPenilaian(Request $request)
{
$tahunDipilih = $request->input('tahun', date('Y'));
    $modul = $request->input('modul', 'spbe'); // Tangkap modul

    $res = PenilaianHelper::calculateIndices($tahunDipilih, $modul);
    if (is_numeric($tahunDipilih)) {
        $tahunLalu = $tahunDipilih - 1;
        $resLalu = PenilaianHelper::calculateIndices($tahunLalu);

        $res['spbe_lama'] = $resLalu['spbe'] ?? 0;

        if (isset($res['domain']) && is_array($res['domain'])) {
            foreach ($res['domain'] as $id_dom => $data_dom) {
                $nilaiLama = $resLalu['domain'][$id_dom] ?? 0;

               if (!is_array($data_dom)) {
                    $res['domain'][$id_dom] = [
                        'indeks_domain' => $data_dom,
                        'indeks_domain_lama' => $nilaiLama
                    ];
                } else {
                    $res['domain'][$id_dom]['indeks_domain_lama'] = $nilaiLama;
                }
            }
        }

        if (isset($res['aspek']) && is_array($res['aspek'])) {
            foreach ($res['aspek'] as $id_dom => $daftar_aspek) {
                if (!is_array($daftar_aspek)) continue;

                foreach ($daftar_aspek as $index => $data_aspek) {
                    $nilaiLama = $resLalu['aspek'][$index] ?? ($resLalu['aspek'][$id_dom][$index] ?? 0);

                    if (!is_array($data_aspek)) {
                        $res['aspek'][$id_dom][$index] = [
                            'indeks_aspek' => $data_aspek,
                            'indeks_aspek_lama' => $nilaiLama
                        ];
                    } else {
                        $res['aspek'][$id_dom][$index]['indeks_aspek_lama'] = $nilaiLama;
                    }
                }
            }
        }
    }

    $tahunList = DB::table('penilaian_kriteria')
        ->where('status', 'final')
        ->select('tahun')
        ->distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    if ($tahunList->isEmpty()) {
        $tahunList = collect([date('Y')]);
    }

    $allDomains = Domain::pluck('nama_domain', 'id_domain')->toArray();

    return view('admin.hasil', compact('res', 'tahunDipilih', 'tahunList', 'allDomains'));
}
public function monitorAdmin(Request $request)
{
   $tahun = $request->input('tahun', date('Y'));

    $domains = PenilaianHelper::getHierarchy($tahun);
    
   $draft = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->select('id_indikator', 
            DB::raw('MAX(nilai_target) as nilai_target'),
            DB::raw('MAX(nilai_asesor_internal) as nilai_asesor_internal'),
            DB::raw('MAX(nilai_verifikator_internal) as nilai_verifikator_internal'),
            DB::raw('MAX(nilai_asesor_external) as nilai_asesor_external'),
            DB::raw('MAX(nilai_akhir_external) as nilai_akhir_external')
        )
        ->groupBy('id_indikator')
        ->get()
        ->keyBy('id_indikator');

    $finalizedYears = HasilIndeks::pluck('tahun')->toArray();
    $startYear = 2020;
    $currentYear = date("Y");

    return view('admin.monitor', compact(
        'domains', 'tahun', 'draft', 'finalizedYears', 'startYear', 'currentYear'
    ));
}
public function targetP2(Request $request)
{
    $modul = $request->input('modul', 'spbe');
    
    // 1. Tersaring berdasarkan modul (Tabel Domain punya kolom modul)
    $availableYears = Domain::where('modul', $modul)
        ->distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    $tahun = $request->input('tahun', $availableYears->first() ?? date('Y'));

    // 2. Perbaikan Eager Loading
    $domains = Domain::where('modul', $modul) // Filter utama di sini
        ->where('tahun', $tahun)
        ->with(['aspek' => function($q) use ($tahun) {
            // Hapus filter modul di sini karena tabel 'aspek' tidak punya kolom 'modul'
            $q->where('tahun', $tahun) 
              ->orderBy('urutan', 'asc');
        }, 'aspek.indikator' => function($q) use ($tahun) {
            // Hapus filter modul di sini karena tabel 'indikator' tidak punya kolom 'modul'
            $q->where('tahun', $tahun)
              ->orderBy('urutan', 'asc');
        }])
        ->orderBy('urutan', 'asc')
        ->get();

    // 3. Draft tetap butuh modul (Pastikan tabel 'penilaian_kriteria' punya kolom modul)
    $draft = DB::table('penilaian_kriteria')
        ->where('modul', $modul)
        ->where('tahun', $tahun)
        ->whereNotNull('nilai_target')
        ->where('nilai_target', '>', 0)
        ->select('id_indikator', DB::raw('MAX(nilai_target) as max_target'))
        ->groupBy('id_indikator')
        ->get()
        ->pluck('max_target', 'id_indikator');

    // 4. Finalized years tetap butuh modul
    $finalizedYears = DB::table('penilaian_kriteria')
        ->where('modul', $modul)
        ->whereIn('status_target', ['draft', 'final', 'verified'])
        ->distinct()
        ->pluck('tahun')
        ->toArray();

    return view('p2.target', compact(
        'domains', 'tahun', 'modul', 'draft', 'finalizedYears', 'availableYears' 
    ));
}
public function finalisasiTarget(Request $request)
{
    $tahun = $request->tahun;
    $modul = $request->input('modul', 'spbe'); 

    try {
        DB::beginTransaction();

        // 1. Ambil semua aspek yang terhubung dengan domain modul terkait
        $aspeksInModul = DB::table('aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('domain.modul', $modul)
            ->where('domain.tahun', $tahun)
            ->select('aspek.id_aspek', 'aspek.id_domain')
            ->get();

        if ($aspeksInModul->isEmpty()) {
            throw new \Exception("Data Aspek untuk modul $modul tahun $tahun tidak ditemukan.");
        }

        foreach ($aspeksInModul as $asp) {
            // Pastikan bobot_aspek ada (kunci utama perhitungan)
            DB::table('bobot_aspek')->updateOrInsert(
                ['id_aspek' => $asp->id_aspek],
                ['bobot' => 5.0] // Default bobot jika belum ada
            );

            // Pastikan bobot_domain ada
            DB::table('bobot_domain')->updateOrInsert(
                ['id_domain' => $asp->id_domain],
                ['bobot' => 25.0] 
            );
        }

        // 2. Update status_target menjadi final untuk modul ini
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->where('modul', $modul)
            ->update(['status_target' => 'final']);

        // 3. Kalkulasi
        $hasil = PenilaianHelper::calculateTarget($tahun, $modul);

        if (!isset($hasil['target_spbe']) || $hasil['target_spbe'] <= 0) {
            throw new \Exception('Gagal: Hasil kalkulasi 0. Pastikan nilai target indikator sudah diisi.');
        }

        // 4. Simpan ke hasil_indeks
        DB::table('hasil_indeks')->updateOrInsert(
            ['tahun' => $tahun, 'modul' => $modul],
            ['target_spbe' => $hasil['target_spbe'], 'updated_at' => now()]
        );

        DB::commit();
        return back()->with('success', 'Finalisasi Berhasil! Nilai: ' . $hasil['target_spbe']);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
public function sinkronisasiBobot($tahun)
{
$aspeks = DB::table('aspek')
    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
    ->where('domain.tahun', $tahun)
    ->where('domain.modul', $modul) // Tambahkan parameter modul di fungsi ini
    ->select('aspek.*')
    ->get();
        foreach ($aspeks as $aspek) {
        $refBobot = DB::table('bobot_aspek')
            ->join('aspek', 'bobot_aspek.id_aspek', '=', 'aspek.id_aspek')
            ->where('aspek.nama_aspek', $aspek->nama_aspek)
            ->where('aspek.id_aspek', '!=', $aspek->id_aspek)
            ->value('bobot_aspek.bobot');

        if ($refBobot) {
            DB::table('bobot_aspek')->updateOrInsert(
                ['id_aspek' => $aspek->id_aspek],
                ['bobot' => $refBobot]
            );
        }
    }

    $domains = DB::table('domain')->where('tahun', $tahun)->get();
    foreach ($domains as $dom) {
        $refBobotDom = DB::table('bobot_domain')
            ->join('domain', 'bobot_domain.id_domain', '=', 'domain.id_domain')
            ->where('domain.nama_domain', $dom->nama_domain)
            ->where('domain.id_domain', '!=', $dom->id_domain)
            ->value('bobot_domain.bobot');

        if ($refBobotDom) {
            DB::table('bobot_domain')->updateOrInsert(
                ['id_domain' => $dom->id_domain],
                ['bobot' => $refBobotDom]
            );
        }
    }
}
// public function finalisasiVerifikator(Request $request)
// {
//     $tahun = $request->input('tahun');
//     try {
//         DB::beginTransaction();

//         DB::table('penilaian_kriteria')
//             ->where('tahun', $tahun)
//             ->update(['status_vrifU' => 'final']);

//         $hasil = PenilaianHelper::calculateVerifikator($tahun);

//         DB::commit();
//         return redirect()->back()->with('success', "Data Berhasil Dikunci!");
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return redirect()->back()->with('error', $e->getMessage());
//     }
// }
public function dashboardP2(Request $request)
{
    // 1. Ambil Parameter Input (Default: SPBE & Tahun All)
    $modul = $request->input('modul', 'spbe');
    $tahunDipilih = $request->input('tahun', 'all');

    // 2. Inisialisasi Variabel Default (PENTING: Mencegah Error Undefined Variable di Blade)
    $mixedLabels = collect([]);
    $mixedValues = collect([]);
    $lineChartLabels = [];
    $lineChartDatasets = [];
    $barChartDatasets = [];
    $radarLabels = [];
    $radarData = [];
    $radarTarget = [];
    $indikatorLabels = collect([]);
    $doughnutData = [];
    $tahunMaster = date('Y');

    // 3. Ambil Daftar Tahun yang Memiliki Data Final & Bernilai pada Modul Terkait
    $tahunList = DB::table('penilaian_kriteria')
                    ->where('modul', $modul)
                    ->where('status', 'final')
                    ->whereNotNull('nilai_asesor_internal') 
                    ->where('nilai_asesor_internal', '>', 0)
                    ->select('tahun')
                    ->distinct()
                    ->orderBy('tahun', 'asc')
                    ->pluck('tahun')
                    ->toArray();

    $lineChartLabels = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];

    // 4. Jika Data Kosong, Langsung Return View dengan Variabel Kosong
    if (empty($tahunList)) {
        return view('p2.dashboard', compact(
            'modul', 'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
            'lineChartLabels', 'lineChartDatasets', 'barChartDatasets',
            'radarLabels', 'radarData', 'radarTarget', 'indikatorLabels', 'doughnutData', 'tahunMaster'
        ));
    }

    // 5. Query Indeks Keseluruhan (Mixed Chart)
    $queryIndeks = HasilIndeks::where('modul', $modul)->orderBy('tahun');
    if ($tahunDipilih !== 'all') {
        $queryIndeks->where('tahun', $tahunDipilih);
    }
    $hasilIndeks = $queryIndeks->get();
    $mixedLabels = $hasilIndeks->pluck('tahun');
    $mixedValues = $hasilIndeks->pluck('indeks_spbe');

    // 6. Penentuan Tahun Master untuk Struktur Domain/Aspek
    $tahunMaster = ($tahunDipilih === 'all') ? max($tahunList) : $tahunDipilih;

    // 7. Ambil Master Data Hierarchy (Domain, Aspek, Indikator) filtered by Modul
    $domainList = Domain::where('modul', $modul)->where('tahun', $tahunMaster)
        ->whereHas('aspek.indikator.penilaian', function($q) use ($tahunMaster, $modul) {
            $q->where('tahun', $tahunMaster)->where('modul', $modul)->whereNotNull('nilai_asesor_internal');
        })->orderBy('urutan')->get();

$aspekList = Aspek::whereHas('domain', function($q) use ($modul) {
        $q->where('modul', $modul); 
    })
    ->where('tahun', $tahunMaster)
    ->whereHas('indikator.penilaian', function($q) use ($tahunMaster, $modul) {
        $q->where('tahun', $tahunMaster)
          ->where('modul', $modul)
          ->whereNotNull('nilai_asesor_internal');
    })
    ->orderBy('urutan')
    ->get();

$indikators = Indikator::whereHas('aspek.domain', function($q) use ($modul) {
        $q->where('modul', $modul);
    })
    ->where('tahun', $tahunMaster)
    ->whereHas('penilaian', function($q) use ($tahunMaster, $modul) {
        $q->where('tahun', $tahunMaster)
          ->where('modul', $modul)
          ->whereNotNull('nilai_asesor_internal');
    })
    ->orderBy('urutan')
    ->get();

    // 8. Kalkulasi Data Line Chart (Domain)
    $namaDomainUnik = $domainList->pluck('nama_domain')->unique();
    foreach ($namaDomainUnik as $nama) {
        $nilaiPerTahun = [];
        $loopTahun = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];
        $hasValue = false;
        
        foreach ($loopTahun as $th) {
            $nilaiAsesor = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                ->where('penilaian_kriteria.modul', $modul)
                ->where('penilaian_kriteria.tahun', $th)
                ->where('penilaian_kriteria.status', 'final')
                ->where('domain.nama_domain', $nama) 
                ->avg('penilaian_kriteria.nilai_asesor_internal');

            if ($nilaiAsesor > 0) $hasValue = true;
            $nilaiPerTahun[] = round($nilaiAsesor ?? 0, 2);
        }

        if ($hasValue) {
            $lineChartDatasets[] = [
                'label' => $nama,
                'data' => $nilaiPerTahun,
                'borderWidth' => 2,
            ];
        }
    }

    // 9. Kalkulasi Data Bar Chart (Aspek)
    $namaAspekUnik = $aspekList->pluck('nama_aspek')->unique();
    foreach ($namaAspekUnik as $nama) {
        $nilaiPerTahun = [];
        $loopTahun = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];
        $hasValue = false;

        foreach ($loopTahun as $th) {
            $nilaiAsesor = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->where('penilaian_kriteria.modul', $modul)
                ->where('penilaian_kriteria.tahun', $th)
                ->where('penilaian_kriteria.status', 'final')
                ->where('aspek.nama_aspek', $nama)
                ->avg('penilaian_kriteria.nilai_asesor_internal');

// Tambahkan join ke tabel domain untuk mengakses kolom modul
$dataAspek = DB::table('aspek')
    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
    ->where('domain.modul', $modul) // Filter modul dari tabel domain
    ->where('aspek.tahun', $th)
    ->where('aspek.nama_aspek', $nama)
    ->select('aspek.*') // Pastikan hanya mengambil kolom dari tabel aspek
    ->first();            $nilaiVerif = $dataAspek->aspek_verif ?? null;
            $hasilAkhir = (!is_null($nilaiVerif)) ? $nilaiVerif : ($nilaiAsesor ?? 0);

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

    // 10. Kalkulasi Data Radar Chart (Aspek Aktif)
    $tahunAktif = ($tahunDipilih === 'all') ? max($tahunList) : $tahunDipilih;
// Perbaikan pada query aspeksRadar
$aspeksRadar = DB::table('aspek')
    ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain') // Hubungkan ke tabel domain
    ->where('domain.modul', $modul)                             // Ambil modul dari tabel domain
    ->where('aspek.tahun', $tahunAktif)
    ->select('aspek.*')                                         // Ambil semua kolom milik aspek saja
    ->orderBy('aspek.urutan', 'asc')
    ->get();
    foreach ($aspeksRadar as $asp) {
        $radarLabels[] = $asp->nama_aspek;
        $nilaiRealisasi = DB::table('penilaian_kriteria as pk')
            ->join('indikator as i', 'pk.id_indikator', '=', 'i.id_indikator')
            ->where('pk.modul', $modul)
            ->where('i.id_aspek', $asp->id_aspek)
            ->where('pk.tahun', $tahunAktif)
            ->where('pk.status', 'final')
            ->avg('pk.nilai_asesor_internal');

        $radarData[] = round($nilaiRealisasi ?? 0, 2);
        $radarTarget[] = (float)($asp->target ?? 0);
    }

    // 11. Kalkulasi Data Doughnut Chart (Indikator)
    $namaIndikatorUnik = $indikators->pluck('nama_indikator')->unique();
    $indikatorLabels = $namaIndikatorUnik->values();
    foreach ($namaIndikatorUnik as $nama) {
        $query = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->where('penilaian_kriteria.modul', $modul)
            ->where('penilaian_kriteria.status', 'final')
            ->where('indikator.nama_indikator', $nama);
            
        $query->where('penilaian_kriteria.tahun', ($tahunDipilih !== 'all' ? $tahunDipilih : $tahunAktif));
        $nilai = $query->avg('penilaian_kriteria.nilai_asesor_internal');
        $doughnutData[] = round($nilai ?? 0, 2);
    }

    // 12. Return View dengan Data Lengkap
    return view('p2.dashboard', compact(
        'modul', 'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
        'lineChartLabels', 'lineChartDatasets', 'barChartDatasets',
        'radarLabels', 'radarData', 'radarTarget', 'indikatorLabels', 'doughnutData', 'tahunMaster'
    ));
}
public function finalisasiEksternal(Request $request)
{
    $tahun = $request->input('tahun') ?? date('Y');
    $modul = $request->input('modul', 'spbe');
    try {
        DB::beginTransaction();
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->where('modul', $modul)
            ->update([
                'status' => 'final',
                'updated_at' => now()
            ]);

        $resEksternal = PenilaianHelper::calculateEksternal($tahun, $modul);
        $resAkhir = PenilaianHelper::calculateAkhirEksternal($tahun, $modul);

        DB::commit();

        $indeksEksternal = $resEksternal['spbe_eksternal'] ?? 0;
        $indeksAkhir = $resAkhir['spbe_akhir'] ?? 0;

        return redirect()->back()->with('success', 
            "Finalisasi Berhasil! Modul: ".strtoupper($modul).", Tahun: $tahun. Indeks Eksternal: $indeksEksternal, Indeks Akhir: $indeksAkhir"
        );

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', "Gagal kalkulasi: " . $e->getMessage());
    }
}
}