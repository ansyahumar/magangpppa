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
  $tahun = $request->input('tahun', date('Y'));

  $availableYears = Domain::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
    
    $finalizedYears = PenilaianIndikator::where('status', 'final')
        ->distinct()
        ->pluck('tahun')
        ->toArray();
   $domains = PenilaianHelper::getHierarchy($tahun);

    $locked = HasilIndeks::where('tahun', $tahun)->exists();
    
    $draft = PenilaianIndikator::where('tahun', $tahun)
        ->get()
        ->keyBy('id_indikator');
    
    $domains = Domain::where('tahun', $tahun)
    ->with(['aspek' => function($q) use ($tahun) {
        $q->where('tahun', $tahun)->orderBy('urutan');
    }, 'aspek.indikator' => function($q) use ($tahun) {
        $q->where('tahun', $tahun)->orderBy('urutan');
    }])
    ->orderBy('urutan')
    ->get();

    return view('penilaian.form', compact(
        'domains',
        'tahun',
        'locked',
        'draft',
        'availableYears',
        'finalizedYears'
    ));
}

public function process(Request $request)
{
    $tahun = $request->input('tahun', date('Y'));
    $user = Auth::user();

    try {
        DB::beginTransaction();
        $userAllowedIds = explode(',', $user->no_id ?? '');
        $userAllowedIds = array_map('trim', $userAllowedIds);

        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->whereIn('id_indikator', function($query) use ($userAllowedIds) {
                $query->select('id_indikator')
                      ->from('indikator')
                      ->whereIn('nomor_indikator', $userAllowedIds);
            })
            ->update(['status' => 'draft']);

        $totalIndikator = DB::table('indikator')->where('tahun', $tahun)->count();
        $totalFinal = DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->where('status', 'draft')
            ->count();

        if ($totalFinal >= $totalIndikator) {
            DB::table('penilaian_kriteria')->where('tahun', $tahun)->update(['status' => 'final']);
            DB::table('penilaian_indikator')->where('tahun', $tahun)->update(['status' => 'final']);
            
            $result = PenilaianHelper::calculateIndices($tahun);
            
            DB::commit();
            return response()->json(['message' => 'Lengkap! Seluruh penilaian tahun ' . $tahun . ' telah dikirim ke Verifikator.'], 200);
        }

        DB::commit();
        return response()->json(['message' => 'Berhasil! Jatah Unit Kerja Anda telah difinalisasi. Menunggu unit kerja lain melengkapi.'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}
  
public function hasilPenilaian(Request $request)
{
    $tahunDipilih = $request->input('tahun', date('Y'));

    $res = PenilaianHelper::calculateIndices($tahunDipilih);

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
    $availableYears = Domain::distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    $tahun = $request->input('tahun', $availableYears->first() ?? date('Y'));

    $domains = Domain::where('tahun', $tahun)
        ->with(['aspek' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
        }, 'aspek.indikator' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
        }])
        ->orderBy('urutan', 'asc')
        ->get();

    $draft = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->whereNotNull('nilai_target')
        ->where('nilai_target', '>', 0)
        ->select('id_indikator', DB::raw('MAX(nilai_target) as max_target'))
        ->groupBy('id_indikator')
        ->get()
        ->pluck('max_target', 'id_indikator');

   
    $finalizedYears = DB::table('penilaian_kriteria')
      ->whereIn('status_target',['draft', 'final', 'verified'])
        ->distinct()
        ->pluck('tahun')
        ->toArray();

    return view('p2.target', compact(
        'domains', 
        'tahun', 
        'draft', 
        'finalizedYears', 
        'availableYears' 
    ));
}
public function finalisasiTarget(Request $request)
{
    $tahun = $request->tahun;

    try {
        DB::beginTransaction();

$aspeksInYear = DB::table('aspek')->where('tahun', $tahun)->get();

foreach ($aspeksInYear as $asp) {
 $existsAspek = DB::table('bobot_aspek')->where('id_aspek', $asp->id_aspek)->exists();
    if (!$existsAspek) {
        DB::table('bobot_aspek')->insert([
            'id_aspek' => $asp->id_aspek,
            'bobot'    => 5.0 
        ]);
    }

    $existsDomain = DB::table('bobot_domain')->where('id_domain', $asp->id_domain)->exists();
    if (!$existsDomain) {
        DB::table('bobot_domain')->insert([
            'id_domain' => $asp->id_domain,
            'bobot'     => 25.0 
        ]);
    }
}
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->where('nilai_target', '>', 0)
            ->update(['status_target' => 'draft']);

       $this->sinkronisasiBobot($tahun);
        $hasil = PenilaianHelper::calculateTarget($tahun);

        if ($hasil['target_spbe'] <= 0) {
             throw new \Exception('Kalkulasi tetap 0. Periksa nilai bobot pada ID Aspek tahun ini di tabel bobot_aspek.');
        }

        DB::table('hasil_indeks')->updateOrInsert(
            ['tahun' => $tahun],
            ['target_spbe' => $hasil['target_spbe'], 'updated_at' => now()]
        );

        DB::commit();
        return back()->with('success', 'Berhasil! Target SPBE: ' . $hasil['target_spbe']);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
public function sinkronisasiBobot($tahun)
{
    $aspeks = DB::table('aspek')->where('tahun', $tahun)->get();
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
public function finalisasiVerifikator(Request $request)
{
    $tahun = $request->input('tahun');
    try {
        DB::beginTransaction();

        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->update(['status_vrifU' => 'final']);

        $hasil = PenilaianHelper::calculateVerifikator($tahun);

        DB::commit();
        return redirect()->back()->with('success', "Data Berhasil Dikunci!");
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}
public function dashboardP2(Request $request)
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
                    
    $lineChartLabels = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];
    $lineChartDatasets = [];
    $barChartDatasets = [];
    $radarLabels = [];
    $radarData = [];
    $radarTarget = [];
    $indikatorLabels = collect([]);
    $doughnutData = [];
    $mixedLabels = collect([]);
    $mixedValues = collect([]);

    if (empty($tahunList)) {
        return view('p2.dashboard', compact(
            'tahunDipilih', 'tahunList', 'mixedLabels', 'mixedValues',
            'lineChartLabels', 'lineChartDatasets', 'barChartDatasets',
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
    $domainList = Domain::where('tahun', $tahunMaster)
        ->whereHas('aspek.indikator.penilaian', function($q) use ($tahunMaster) {
            $q->where('tahun', $tahunMaster)->whereNotNull('nilai_asesor_internal');
        })->orderBy('urutan')->get();

    $aspekList = Aspek::where('tahun', $tahunMaster)
        ->whereHas('indikator.penilaian', function($q) use ($tahunMaster) {
            $q->where('tahun', $tahunMaster)->whereNotNull('nilai_asesor_internal');
        })->orderBy('urutan')->get();

    $indikators = Indikator::where('tahun', $tahunMaster)
        ->whereHas('penilaian', function($q) use ($tahunMaster) {
            $q->where('tahun', $tahunMaster)->whereNotNull('nilai_asesor_internal');
        })->orderBy('urutan')->get();

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

    $namaAspekUnik = $aspekList->pluck('nama_aspek')->unique();
    foreach ($namaAspekUnik as $nama) {
        $nilaiPerTahun = [];
        $loopTahun = ($tahunDipilih === 'all') ? $tahunList : [$tahunDipilih];
        $hasValue = false;

        foreach ($loopTahun as $th) {
            $nilaiAsesor = DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->where('penilaian_kriteria.tahun', $th)
                ->where('penilaian_kriteria.status', 'final')
                ->where('aspek.nama_aspek', $nama)
                ->avg('penilaian_kriteria.nilai_asesor_internal');

            $dataAspek = DB::table('aspek')->where('tahun', $th)->where('nama_aspek', $nama)->first();
            $nilaiVerif = $dataAspek->aspek_verif ?? null;
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

    $tahunAktif = ($tahunDipilih === 'all') ? (empty($tahunList) ? date('Y') : max($tahunList)) : $tahunDipilih;
    
    $aspeksRadar = DB::table('aspek')
        ->where('tahun', $tahunAktif)
        ->orderBy('urutan', 'asc')
        ->get();

    foreach ($aspeksRadar as $asp) {
        $radarLabels[] = $asp->nama_aspek;
        $nilaiRealisasi = DB::table('penilaian_kriteria as pk')
            ->join('indikator as i', 'pk.id_indikator', '=', 'i.id_indikator')
            ->where('i.id_aspek', $asp->id_aspek)
            ->where('pk.tahun', $tahunAktif)
            ->where('pk.status', 'final')
            ->avg('pk.nilai_asesor_internal');

        $radarData[] = round($nilaiRealisasi ?? 0, 2);
        $radarTarget[] = (float)($asp->target ?? 0);
    }

    $namaIndikatorUnik = $indikators->pluck('nama_indikator')->unique();
    $indikatorLabels = $namaIndikatorUnik->values();
    $doughnutData = [];

    foreach ($namaIndikatorUnik as $nama) {
        $query = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->where('penilaian_kriteria.status', 'final')
            ->where('indikator.nama_indikator', $nama);
            
        if ($tahunDipilih !== 'all') {
            $query->where('penilaian_kriteria.tahun', $tahunDipilih);
        } else {
            $query->where('penilaian_kriteria.tahun', $tahunAktif);
        }

        $nilai = $query->avg('penilaian_kriteria.nilai_asesor_internal');
        $doughnutData[] = round($nilai ?? 0, 2);
    }

    $tahunTerbaruDB = DB::table('aspek')->max('tahun');

    return view('p2.dashboard', compact(
        'tahunDipilih', 
        'tahunList', 
        'mixedLabels', 
        'mixedValues',
        'lineChartLabels', 
        'lineChartDatasets', 
        'barChartDatasets',
        'radarLabels', 
        'radarData', 
        'radarTarget', 
        'indikatorLabels', 
        'doughnutData',
        'tahunMaster',    
        'tahunTerbaruDB'  
    ));
}
public function finalisasiEksternal(Request $request)
{
    $tahun = $request->input('tahun') ?? date('Y');

    try {
        DB::beginTransaction();
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->update([
                'status' => 'final',
                'updated_at' => now()
            ]);

        $resEksternal = PenilaianHelper::calculateEksternal($tahun);
        $resAkhir = PenilaianHelper::calculateAkhirEksternal($tahun);

        DB::commit();

        $indeksEksternal = $resEksternal['spbe_eksternal'] ?? 0;
        $indeksAkhir = $resAkhir['spbe_akhir'] ?? 0;

        return redirect()->back()->with('success', 
            "Finalisasi Berhasil! Tahun: $tahun. Indeks Eksternal: $indeksEksternal, Indeks Akhir: $indeksAkhir"
        );

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', "Gagal kalkulasi: " . $e->getMessage());
    }
}
}