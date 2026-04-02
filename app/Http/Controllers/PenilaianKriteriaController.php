<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PenilaianKriteriaController extends Controller
{
public function show(Request $request, $id_indikator)
{
    try {
        $tahunAktif = $request->query('tahun') ?? now()->year;
        $currentIndikator = DB::table('indikator')
            ->where('id_indikator', $id_indikator)
            ->first();

        if (!$currentIndikator) {
            throw new \Exception("Indikator tidak ditemukan.");
        }

        $detailIndikator = DB::table('indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('indikator.id_indikator', $id_indikator)
            ->select('domain.nama_domain', 'aspek.nama_aspek', 'indikator.*')
            ->first();

        $allRelatedIndikatorIds = DB::table('indikator')
            ->where('nomor_indikator', $currentIndikator->nomor_indikator)
            ->pluck('id_indikator');

        $historiData = DB::table('penilaian_kriteria')
            ->join('kriteria', 'penilaian_kriteria.id_kriteria', '=', 'kriteria.id_kriteria')
            ->whereIn('penilaian_kriteria.id_indikator', $allRelatedIndikatorIds)
            ->where('penilaian_kriteria.tahun', '<', $tahunAktif)
            ->select('penilaian_kriteria.*', 'kriteria.nama_kriteria')
            ->orderBy('penilaian_kriteria.tahun', 'desc')
            ->get()
            ->groupBy('nama_kriteria');

        $penilaianAktif = DB::table('penilaian_kriteria')
            ->where('id_indikator', $id_indikator)
            ->where('tahun', $tahunAktif)
            ->get()
            ->keyBy('id_kriteria');

      $kriteria = DB::table('kriteria')
    ->where('id_indikator', $id_indikator)
    ->get()
    ->map(function ($k) use ($tahunAktif, $historiData, $penilaianAktif) {
        $histori = isset($historiData[$k->nama_kriteria]) ? $historiData[$k->nama_kriteria]->first() : null;

        $nilaiPrev = 0;
        if ($histori) {
            if (!empty($histori->nilai_verifikator_internal)) {
                $nilaiPrev = $histori->nilai_verifikator_internal;
            } else {
                $nilaiPrev = $histori->nilai_asesor_internal ?? 0;
            }
        }

        $aktif = $penilaianAktif[$k->id_kriteria] ?? null;

        return [
            'id_kriteria'                => $k->id_kriteria,
            'nama_kriteria'              => $k->nama_kriteria,
            'nilai_target'               => $aktif ? $aktif->nilai_target : null,
            'nilai_asesor_internal'      => $aktif ? $aktif->nilai_asesor_internal : null,
            'nilai_verifikator_internal' => $aktif ? $aktif->nilai_verifikator_internal : null,
            'nilai_asesor_external'      => $aktif ? $aktif->nilai_asesor_external : null, 
            'nilai_akhir_external'       => $aktif ? $aktif->nilai_akhir_external : null,  
            'nilai_histori'              => (float) $nilaiPrev,
            'nilai_display'              => $aktif ? ($aktif->nilai_verifikator_internal ?: $aktif->nilai_asesor_internal ?: 0) : 0
        ];
    });

        $catatan = DB::table('catatan_kriteria')
            ->where([
                'id_indikator' => $id_indikator,
                'tahun'        => $tahunAktif
            ])
            ->first();

        $logs = [];
        if ($catatan) {
            $logs = DB::table('catatan_logs')
                ->where('id_catatan', $catatan->id_catatan)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $is_final = DB::table('penilaian_indikator')
            ->where([
                'id_indikator' => $id_indikator,
                'tahun'        => $tahunAktif,
                'status'       => 'final'
            ])
            ->exists();

        $tahunHistoriLabel = $historiData->flatten()->max('tahun') ?? ($tahunAktif - 1);

        return response()->json([
            'detail'        => $detailIndikator,
            'tahun_aktif'   => (int) $tahunAktif,
            'tahun_histori' => (int) $tahunHistoriLabel,
            'kriteria'      => $kriteria,
            'catatan'       => $catatan ? [$catatan] : [],
            'logs'          => $logs,
            'mode'          => $is_final ? 'histori' : 'input'
        ]);

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

public function store(Request $request)
{
    $user = Auth::user();
    $tahun = $request->tahun;
    $id_indikator = $request->id_indikator;
    $isEditMode = $request->input('is_edit_mode') === '1';
    $buktiDiklik = $request->input('bukti_diklik') === '1';
    $catatan_external = $request->catatan_external;
    $catatanData = json_decode($request->input('catatan'), true) ?? [];
    $kriteriaData = json_decode($request->input('kriteria'), true) ?? [];
    $pencapaianInput = $request->input('pencapaian');

    try {
        DB::beginTransaction();
        if (empty($kriteriaData)) {
        throw new \Exception('Data kriteria tidak ditemukan.');
    }

    $firstKriteria = reset($kriteriaData);
        $firstKriteria = reset($kriteriaData);
        $kriteriaId = $firstKriteria['kriteria_id'] ?? null;
        $existingRow = DB::table('penilaian_kriteria')
            ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
            ->first();

        foreach ($kriteriaData as $data) {
            $updateData = ['updated_at' => now()];

            if ($user->role === 'verifikator') {
                if (!$existingRow || is_null($existingRow->nilai_asesor_internal)) {
                    throw new \Exception('Verifikator belum bisa memberi nilai karena User belum mengisi penilaian.');
                }
                $updateData['nilai_verifikator_internal'] = $data['nilai_verifikator_internal'];
                $updateData['id_kriteria'] = $data['kriteria_id']; 
                $updateData['status'] = 'final'; 
            } 
            elseif ($user->role === 'admin') {
                if (isset($data['nilai_asesor_external'])) $updateData['nilai_asesor_external'] = $data['nilai_asesor_external'];
                if (isset($data['nilai_akhir_external'])) {
                    $updateData['nilai_akhir_external'] = $data['nilai_akhir_external'];
                    $updateData['id_kriteria'] = $data['kriteria_id'];
                }
                $updateData['status'] = 'final';
            } 
            elseif ($user->role === 'user') { 
                $updateData['nilai_asesor_internal'] = $data['nilai_asesor_internal'];
                $updateData['id_kriteria'] = $data['kriteria_id']; 
                $updateData['status'] = 'draft';
            }
            elseif ($user->role === 'p2') {
                $updateData['nilai_target'] = $data['nilai_target'];
                $updateData['id_kriteria'] = $data['kriteria_id'];
                $updateData['status'] = 'draft';
            }

            if ($existingRow) {
                DB::table('penilaian_kriteria')
                    ->where('id_penilaian', $existingRow->id_penilaian)
                    ->update($updateData);
            } else {
                $updateData['id_indikator'] = $id_indikator;
                $updateData['tahun'] = $tahun;
                $updateData['id_kriteria'] = $data['kriteria_id'];
                $updateData['created_at'] = now();
                DB::table('penilaian_kriteria')->insert($updateData);
                
                $existingRow = DB::table('penilaian_kriteria')
                    ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
                    ->first();
            }
        }

       $current = DB::table('penilaian_kriteria')
    ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
    ->first();

$nilaiDashboardInternal = $current->nilai_verifikator_internal 
                        ?? $current->nilai_asesor_internal; 

DB::table('penilaian_indikator')->updateOrInsert(
    ['id_indikator' => $id_indikator, 'tahun' => $tahun],
    [
        'nilai' => $nilaiDashboardInternal ?? 0, 
        'updated_at' => now()
    ]
);

        $cData = reset($catatanData) ?: [];
        $existingCatatan = DB::table('catatan_kriteria')
            ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
            ->first();

        $buktiLama = $existingCatatan ? json_decode($existingCatatan->bukti ?? '[]', true) : [];
        $linksBaru = $cData['links'] ?? [];
        $filesBaru = [];

        if ($request->hasFile('file_bukti')) {
            foreach ($request->file('file_bukti') as $file) {
                if ($file->isValid()) {
                    $filesBaru[] = $file->store('bukti_spbe', 'public');
                }
            }
        }

        $totalBukti = array_values(array_unique(array_filter(array_merge($linksBaru, $filesBaru, $buktiLama))));

        DB::table('catatan_kriteria')->updateOrInsert(
            ['id_indikator' => $id_indikator, 'tahun' => $tahun,],
            [
                'nama_catatankriteria' => $cData['nama_catatankriteria'] ?? ($existingCatatan->nama_catatankriteria ?? ''),
                'pencapaian' => $pencapaianInput ?? ($existingCatatan->pencapaian ?? ''),
                'bukti' => json_encode($totalBukti),
                'updated_at' => now(),
                'catatan_external'     => $request->input('catatan_external') ?? ($existingCatatan->catatan_external ?? ''),
                'created_at' => $existingCatatan ? $existingCatatan->created_at : now()
            ]
        );

        $currentCatatan = DB::table('catatan_kriteria')
            ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
            ->first();

        $aksi = match($user->role) {
            'verifikator' => 'Verifikator mengupdate nilai',
            'admin'       => 'Admin melakukan penilaian eksternal',
            'user'        => 'User memperbarui data/bukti',
            'p2'   => 'Karodatin menetapkan target',
            default       => 'Update data'
        };

        DB::table('catatan_logs')->insert([
            'id_catatan'   => $currentCatatan->id_catatan,
            'id_indikator' => $id_indikator,
            'user_id'      => $user->id,
            'name'         => $user->name,
            'role'         => $user->role,
            'tahun'        => $tahun,
            'aksi'         => $aksi,
            'created_at'   => now()
        ]);

        DB::commit();
        return response()->json(['message' => 'Data Berhasil Disimpan'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error Store Penilaian: " . $e->getMessage());
        return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
    }
}
public function getDetailIndikator(Request $request, $id)
{
    try {
        $tahun = $request->query('tahun');

        $detail = DB::table('indikator')
            ->leftJoin('penjelasan_indikator', 'indikator.id_indikator', '=', 'penjelasan_indikator.id_indikator')
            ->where('indikator.id_indikator', $id)
            ->select(
                'indikator.*', 
                'penjelasan_indikator.tatacara_penilaian', 
                'penjelasan_indikator.penjelasan_kriteria',
                'penjelasan_indikator.deskripsi',
                'indikator.urutan',
            )
            ->first();

       $kriteria = DB::table('penilaian_kriteria')->where(['id_indikator' => $id, 'tahun' => $tahun])->get();
        $catatan = DB::table('catatan_kriteria')->where(['id_indikator' => $id, 'tahun' => $tahun])->first();
        
        $logs = [];
        if ($catatan) {
            $logs = DB::table('catatan_logs')
                ->where('id_catatan', $catatan->id_catatan)
                ->whereIn('role', ['user', 'p2', 'verifikator'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'kriteria' => $kriteria,
            'catatan'  => $catatan ? [$catatan] : [],
            'logs'     => $logs,
            'detail'   => $detail,
            'mode'     => ($tahun < date('Y')) ? 'histori' : 'aktif'
        ]);

    } catch (\Exception $e) {
         return response()->json(['message' => $e->getMessage()], 500);
    }
}


public function getKriteriaMaster(Request $request, $id_indikator)
{
    try {
        $tahun = $request->query('tahun', date('Y'));

        $kriteria = DB::table('kriteria')
            ->where('id_indikator', $id_indikator)
            ->where('tahun', $tahun)
            ->orderBy('id_kriteria', 'asc')
            ->get();

        return response()->json([
            'status'   => 'success',
            'kriteria' => $kriteria,
            'tahun'    => $tahun
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
}