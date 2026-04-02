<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\Domain;

class PenilaianHelper 
{
   
    public static function getHierarchy($tahun = null)
{
   
    $tahun = $tahun ?? date('Y');

    return Domain::where('tahun', $tahun)
        ->with([
            'aspek' => function($q) use ($tahun) {
                $q->where('tahun', $tahun);
            },
            'aspek.indikator' => function($q) use ($tahun) {
                $q->where('tahun', $tahun);
            },
            'aspek.bobotAspek',
            'bobotDomain'
        ])
        ->get();
}
   public static function calculateIndices($tahun)
{
    $penilaian = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->where('status', 'final')
        ->get();

    if ($penilaian->isEmpty()) {
        return ['spbe' => 0, 'predikat' => 'Data Kosong'];
    }

   $map = DB::table('aspek')->pluck('id_domain', 'id_aspek')->toArray();

 $n_aspek = DB::table('bobot_aspek')->where('tahun', $tahun)->pluck('bobot', 'id_aspek');
$n_domain = DB::table('bobot_domain')->where('tahun', $tahun)->pluck('bobot', 'id_domain');

    $m_aspek = [];
    $data_per_domain = [];

    $groupedPenilaian = $penilaian->groupBy(function ($item) {
        return DB::table('indikator')->where('id_indikator', $item->id_indikator)->value('id_aspek');
    });

    foreach ($groupedPenilaian as $id_asp => $rows) {
        if (!$id_asp) continue;

        $avg_m = (float) $rows->avg('nilai_asesor_internal');
        $m_aspek[$id_asp] = $avg_m;

        DB::table('aspek_hasil')->updateOrInsert(
            ['id_aspek' => $id_asp, 'tahun' => $tahun],
            ['nilai_aspek' => round($avg_m, 2)]
        );

        $id_dom = $map[$id_asp] ?? null;
        if ($id_dom) {
            $data_per_domain[$id_dom][] = [
                'nilai' => $avg_m,
                'bobot_aspek' => (float) ($n_aspek[$id_asp] ?? 0)
            ];
        }
    }

    $total_spbe = 0;
    $domain_results = [];

    foreach ($data_per_domain as $id_dom => $aspeks) {
        $total_bobot_d = (float) ($n_domain[$id_dom] ?? 0);
        
        if ($total_bobot_d <= 0) {
            $fallback = [1=>13, 2=>25, 3=>16.5, 4=>45.5, 18=>13, 19=>25, 20=>16.5, 21=>45.5];
            $total_bobot_d = $fallback[$id_dom] ?? 0;
        }

        $jumlah_aspek_di_domain = count($aspeks);
        $nilai_domain_kalkulasi = 0;

        if ($total_bobot_d > 0) {
            if ($jumlah_aspek_di_domain === 1) {
                $nilai_domain_kalkulasi = $aspeks[0]['nilai'];
            } else {
                foreach ($aspeks as $item) {
                    $nilai_domain_kalkulasi += ($item['nilai'] * $item['bobot_aspek']) / $total_bobot_d;
                }
            }
        }

        $domain_results[$id_dom] = $nilai_domain_kalkulasi;

        DB::table('domain_hasil')->updateOrInsert(
            ['id_domain' => $id_dom, 'tahun' => $tahun],
            ['nilai_domain' => round($nilai_domain_kalkulasi, 2)]
        );

        $total_spbe += ($nilai_domain_kalkulasi * ($total_bobot_d / 100));
    }

    $final_spbe = round($total_spbe, 2);

    DB::table('hasil_indeks')->updateOrInsert(
        ['tahun' => $tahun],
        [
            'indeks_spbe' => $final_spbe,
            'predikat' => self::getPredikat($final_spbe),
            'updated_at' => now()
        ]
    );

    return [
        'spbe' => $final_spbe,
        'predikat' => self::getPredikat($final_spbe),
        'domain' => $domain_results,
        'aspek' => $m_aspek
    ];
}

    
public static function calculateTarget($tahun)
    {
         $indikator = DB::table('penilaian_kriteria as pk')
            ->join('indikator as i', 'pk.id_indikator', '=', 'i.id_indikator')
            ->select('i.id_aspek', 'pk.nilai_target')
            ->where('pk.tahun', $tahun)
            ->where('pk.nilai_target', '>', 0)
            ->get();

        if ($indikator->isEmpty()) {
            return ['target_spbe' => 0];
        }

         $map = DB::table('aspek')->pluck('id_domain', 'id_aspek')->toArray();

      $n_aspek = DB::table('bobot_aspek')->where('tahun', $tahun)->pluck('bobot', 'id_aspek');
$n_domain = DB::table('bobot_domain')->where('tahun', $tahun)->pluck('bobot', 'id_domain');

        $m_aspek = [];
        $data_per_domain = [];

         foreach ($indikator->groupBy('id_aspek') as $id_asp => $rows) {
            $avg_m = (float) $rows->avg('nilai_target');
            $m_aspek[$id_asp] = $avg_m;

            DB::table('aspek_hasil')->updateOrInsert(
                ['id_aspek' => $id_asp, 'tahun' => $tahun],
                ['target' => round($avg_m, 2)]
            );

            $id_dom = $map[$id_asp] ?? null;
            if ($id_dom) {
                $data_per_domain[$id_dom][] = [
                    'nilai' => $avg_m,
                    'bobot_aspek' => (float) ($n_aspek[$id_asp] ?? 0)
                ];
            }
        }

        $domain_final_results = [];
        $total_spbe = 0;

        foreach ($data_per_domain as $id_dom => $aspeks) {
            $total_bobot_d = (float) ($n_domain[$id_dom] ?? 0);
            
            if ($total_bobot_d <= 0) {
                $fallback = [1=>13, 2=>25, 3=>16.5, 4=>45.5, 18=>13, 19=>25, 20=>16.5, 21=>45.5];
                $total_bobot_d = $fallback[$id_dom] ?? 0;
            }

            $jumlah_aspek_di_domain = count($aspeks);
            $nilai_domain_kalkulasi = 0;

            if ($total_bobot_d > 0) {
                if ($jumlah_aspek_di_domain === 1) {
                    $nilai_domain_kalkulasi = $aspeks[0]['nilai'];
                } else {
                    foreach ($aspeks as $item) {
                        $nilai_domain_kalkulasi += ($item['nilai'] * $item['bobot_aspek']) / $total_bobot_d;
                    }
                }
            }

            $domain_final_results[$id_dom] = $nilai_domain_kalkulasi;

            DB::table('domain_hasil')->updateOrInsert(
                ['id_domain' => $id_dom, 'tahun' => $tahun],
                ['target' => round($nilai_domain_kalkulasi, 2)]
            );

            $total_spbe += ($nilai_domain_kalkulasi * ($total_bobot_d / 100));
        }

        return [
            'target_spbe' => round($total_spbe, 2),
            'domain' => $domain_final_results,
            'aspek' => $m_aspek
        ];
    }

 
    private static function extractBobotValue($model, $relationName)
    {
        if (isset($model->bobot) && is_numeric($model->bobot) && $model->bobot > 0) {
            return (float) $model->bobot;
        }
        $relation = $model->$relationName;
        if ($relation instanceof \Illuminate\Support\Collection) {
            $relation = $relation->first();
        }
        return ($relation && isset($relation->bobot)) ? (float) $relation->bobot : 0;
    }
public static function calculateVerifikator($tahun)
{
     $penilaian = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->where('status', 'final')
        ->get();

    if ($penilaian->isEmpty()) {
        return ['spbe' => 0, 'predikat' => 'Data Kosong'];
    }

    $map = DB::table('aspek')->pluck('id_domain', 'id_aspek')->toArray();

   $n_aspek = DB::table('bobot_aspek')->where('tahun', $tahun)->pluck('bobot', 'id_aspek');
$n_domain = DB::table('bobot_domain')->where('tahun', $tahun)->pluck('bobot', 'id_domain');

    $m_aspek = [];
    $data_per_domain = [];

    $groupedPenilaian = $penilaian->groupBy(function ($item) {
        return DB::table('indikator')->where('id_indikator', $item->id_indikator)->value('id_aspek');
    });

    foreach ($groupedPenilaian as $id_asp => $rows) {
        if (!$id_asp) continue;

        $avg_m = (float) $rows->avg('nilai_verifikator_internal');
        $m_aspek[$id_asp] = $avg_m;

         DB::table('aspek_hasil')->updateOrInsert(
            ['id_aspek' => $id_asp, 'tahun' => $tahun],
            ['aspek_verif' => round($avg_m, 2)]
        );

        $id_dom = $map[$id_asp] ?? null;
        if ($id_dom) {
            $data_per_domain[$id_dom][] = [
                'nilai' => $avg_m,
                'bobot_aspek' => (float) ($n_aspek[$id_asp] ?? 0)
            ];
        }
    }

    $total_spbe = 0;

    foreach ($data_per_domain as $id_dom => $aspeks) {
        $total_bobot_d = (float) ($n_domain[$id_dom] ?? 0);
        
        if ($total_bobot_d <= 0) {
            $fallback = [1=>13, 2=>25, 3=>16.5, 4=>45.5, 18=>13, 19=>25, 20=>16.5, 21=>45.5];
            $total_bobot_d = $fallback[$id_dom] ?? 0;
        }

        $jumlah_aspek_di_domain = count($aspeks);
        $nilai_domain_kalkulasi = 0;

        if ($total_bobot_d > 0) {
            if ($jumlah_aspek_di_domain === 1) {
                $nilai_domain_kalkulasi = $aspeks[0]['nilai'];
            } else {
                foreach ($aspeks as $item) {
                    $nilai_domain_kalkulasi += ($item['nilai'] * $item['bobot_aspek']) / $total_bobot_d;
                }
            }
        }

        DB::table('domain_hasil')->updateOrInsert(
            ['id_domain' => $id_dom, 'tahun' => $tahun],
            ['domain_verif' => round($nilai_domain_kalkulasi, 2)]
        );

         $total_spbe += ($nilai_domain_kalkulasi * ($total_bobot_d / 100));
    }

    $final_spbe = round($total_spbe, 2);

    DB::table('hasil_indeks')->updateOrInsert(
        ['tahun' => $tahun],
        [
            'indeks_verif' => $final_spbe,
            'updated_at' => now()
        ]
    );

    return [
        'spbe_verif' => $final_spbe,
        'predikat_verif' => self::getPredikat($final_spbe)
    ];
}

public static function calculateEksternal($tahun)
{
    $penilaian = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->where('status', 'final')
        ->whereNotNull('nilai_asesor_external')
        ->get();

    if ($penilaian->isEmpty()) return ['spbe_eksternal' => 0];

    $map = DB::table('aspek')->pluck('id_domain', 'id_aspek')->toArray();
    $n_aspek = DB::table('bobot_aspek')->where('tahun', $tahun)->pluck('bobot', 'id_aspek');
    $n_domain = DB::table('bobot_domain')->where('tahun', $tahun)->pluck('bobot', 'id_domain');
    $indikatorMap = DB::table('indikator')->where('tahun', $tahun)->pluck('id_aspek', 'id_indikator');
    $data_per_domain = [];
    $groupedPenilaian = $penilaian->groupBy(fn($item) => $indikatorMap[$item->id_indikator] ?? null);

    foreach ($groupedPenilaian as $id_asp => $rows) {
        if (!$id_asp) continue;
        
        $avg_m = (float) $rows->avg('nilai_asesor_external'); 

        DB::table('aspek_hasil')->updateOrInsert(
            ['id_aspek' => $id_asp, 'tahun' => $tahun],
            ['aspek_eksternal' => round($avg_m, 2)]
        );

        $id_dom = $map[$id_asp] ?? null;
        if ($id_dom) {
            $data_per_domain[$id_dom][] = [
                'nilai' => $avg_m,
                'bobot_aspek' => (float) ($n_aspek[$id_asp] ?? 0)
            ];
        }
    }

    $total_spbe = 0;
    foreach ($data_per_domain as $id_dom => $aspeks) {
        $total_bobot_d = (float) ($n_domain[$id_dom] ?? 0);
        $bobot_tersedia = array_sum(array_column($aspeks, 'bobot_aspek'));
        
        $nilai_domain_kalkulasi = 0;
        if ($bobot_tersedia > 0) {
            foreach ($aspeks as $item) {
                $nilai_domain_kalkulasi += ($item['nilai'] * $item['bobot_aspek']) / $bobot_tersedia;
            }
        }

        DB::table('domain_hasil')->updateOrInsert(
            ['id_domain' => $id_dom, 'tahun' => $tahun],
            ['domain_eksternal' => round($nilai_domain_kalkulasi, 2)]
        );

        $total_spbe += ($nilai_domain_kalkulasi * ($total_bobot_d / 100));
    }

    $final_spbe = round($total_spbe, 2);
    DB::table('hasil_indeks')->updateOrInsert(
        ['tahun' => $tahun],
        ['indeks_eksternal' => $final_spbe, 'updated_at' => now()]
    );

    return ['spbe_eksternal' => $final_spbe];
}

public static function calculateAkhirEksternal($tahun)
{
    $penilaian = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->where('status', 'final')
        ->whereNotNull('nilai_akhir_external')
        ->get();

    if ($penilaian->isEmpty()) return ['spbe_akhir' => 0];

    $map = DB::table('aspek')->pluck('id_domain', 'id_aspek')->toArray();
    $n_aspek = DB::table('bobot_aspek')->where('tahun', $tahun)->pluck('bobot', 'id_aspek');
    $n_domain = DB::table('bobot_domain')->where('tahun', $tahun)->pluck('bobot', 'id_domain');
    $indikatorMap = DB::table('indikator')->where('tahun', $tahun)->pluck('id_aspek', 'id_indikator');

    $data_per_domain = [];

    $groupedPenilaian = $penilaian->groupBy(fn($item) => $indikatorMap[$item->id_indikator] ?? null);

    foreach ($groupedPenilaian as $id_asp => $rows) {
        if (!$id_asp) continue;
        
        $avg_m = (float) $rows->avg('nilai_akhir_external'); 

        DB::table('aspek_hasil')->updateOrInsert(
            ['id_aspek' => $id_asp, 'tahun' => $tahun],
            ['aspek_akhir_eksternal' => round($avg_m, 2)]
        );

        $id_dom = $map[$id_asp] ?? null;
        if ($id_dom) {
            $data_per_domain[$id_dom][] = [
                'nilai' => $avg_m,
                'bobot_aspek' => (float) ($n_aspek[$id_asp] ?? 0)
            ];
        }
    }

    $total_spbe = 0;
    foreach ($data_per_domain as $id_dom => $aspeks) {
        $total_bobot_d = (float) ($n_domain[$id_dom] ?? 0);
        $bobot_tersedia = array_sum(array_column($aspeks, 'bobot_aspek'));

        $nilai_domain_kalkulasi = 0;
        if ($bobot_tersedia > 0) {
            foreach ($aspeks as $item) {
                $nilai_domain_kalkulasi += ($item['nilai'] * $item['bobot_aspek']) / $bobot_tersedia;
            }
        }

        DB::table('domain_hasil')->updateOrInsert(
            ['id_domain' => $id_dom, 'tahun' => $tahun],
            ['domain_akhir_eksternal' => round($nilai_domain_kalkulasi, 2)]
        );

        $total_spbe += ($nilai_domain_kalkulasi * ($total_bobot_d / 100));
    }

    $final_spbe = round($total_spbe, 2);
    DB::table('hasil_indeks')->updateOrInsert(
        ['tahun' => $tahun],
        ['indeks_akhir_eksternal' => $final_spbe, 'updated_at' => now()]
    );

    return ['spbe_akhir' => $final_spbe];
}
 
    private static function getPredikat($nilai)
    {
        if ($nilai >= 4.2) return 'Memuaskan';
        if ($nilai >= 3.5) return 'Sangat Baik';
        if ($nilai >= 2.6) return 'Baik';
        if ($nilai >= 1.8) return 'Cukup';
        return 'Kurang';
    }
}