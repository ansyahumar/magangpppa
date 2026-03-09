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
    $isEditMode = $request->input('is_edit_mode') === '1';
    $buktiDiklik = $request->input('bukti_diklik') === '1';
    $id_indikator = $request->id_indikator;
    $catatanData = json_decode($request->input('catatan'), true) ?? [];
    $kriteriaData = json_decode($request->input('kriteria'), true) ?? [];
    $pencapaianInput = $request->input('pencapaian');

    try {
        DB::beginTransaction();

        foreach ($kriteriaData as $data) {
            $existingRow = DB::table('penilaian_kriteria')
                ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
                ->first();

            $updateData = ['updated_at' => now()];

            if ($user->role === 'verifikator') {
                if (!$existingRow || is_null($existingRow->nilai_asesor_internal)) {
                    throw new \Exception('Maaf, Verifikator belum bisa memberi nilai karena Asesor/User belum mengisi penilaian.');
                }
                $updateData['nilai_verifikator_internal'] = $data['nilai_verifikator_internal'];
                $updateData['id_kriteria'] = $data['kriteria_id']; 
                $updateData['status'] = 'final'; 
            } 
            elseif ($user->role === 'admin') {
                if (!$existingRow) throw new \Exception('Data dasar belum tersedia.');
                if (isset($data['nilai_asesor_external'])) $updateData['nilai_asesor_external'] = $data['nilai_asesor_external'];
                if (isset($data['nilai_akhir_external'])) {
                    $updateData['nilai_akhir_external'] = $data['nilai_akhir_external'];
                    $updateData['id_kriteria'] = $data['kriteria_id'];
                }
            } 
            elseif ($user->role === 'user') { 
                $updateData['nilai_asesor_internal'] = $data['nilai_asesor_internal'];
                $updateData['id_kriteria'] = $data['kriteria_id']; 
                $updateData['status'] = 'draft';
            }
            elseif ($user->role === 'p2') {
                $updateData['nilai_target'] = $data['nilai_target'];
                if (!$existingRow) {
                    $updateData['id_kriteria'] = $data['kriteria_id'];
                }
                $updateData['status'] = 'draft';
            }

            if ($existingRow) {
                DB::table('penilaian_kriteria')->where('id_penilaian', $existingRow->id_penilaian)->update($updateData);
            } else {
                $updateData['id_kriteria'] = $data['kriteria_id'];
                $updateData['id_indikator'] = $id_indikator;
                $updateData['tahun'] = $tahun;
                $updateData['created_at'] = now();
                DB::table('penilaian_kriteria')->insert($updateData);
            }
        }

        $current = DB::table('penilaian_kriteria')
            ->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])
            ->first();

        $nilaiAkhir = $current->nilai_verifikator_internal 
                    ?? $current->nilai_akhir_external 
                    ?? $current->nilai_asesor_internal; 

        DB::table('penilaian_indikator')->updateOrInsert(
            ['id_indikator' => $id_indikator, 'tahun' => $tahun],
            [
                'nilai' => $nilaiAkhir ?? 0, 
                'updated_at' => now()
                
            ]
        );

        
        $cData = reset($catatanData); 
        $cidKey = key($catatanData);
        $existingCatatan = DB::table('catatan_kriteria')->where(['id_indikator' => $id_indikator, 'tahun' => $tahun])->first();
        $buktiLama = $existingCatatan ? json_decode($existingCatatan->bukti ?? '[]', true) : [];
        $oldLinks = array_values(array_filter($buktiLama, fn($b) => str_starts_with($b, 'http')));
        $oldFiles = array_values(array_filter($buktiLama, fn($b) => !str_starts_with($b, 'http')));
        $linksBaru = $cData['links'] ?? [];
        $totalLinks = array_slice(array_unique(array_merge($oldLinks, $linksBaru)), 0, 3);
        $filesBaru = [];
        $uploadedFiles = $request->file('file_bukti') ?? [];
        $targetFiles = isset($uploadedFiles[$cidKey]) ? $uploadedFiles[$cidKey] : $uploadedFiles;
        if (!is_array($targetFiles)) { $targetFiles = [$targetFiles]; }
        $sisaKuota = 3 - count($oldFiles);
        foreach ($targetFiles as $file) {
            if ($sisaKuota <= 0) break;
            if ($file && $file->isValid()) {
                $path = $file->store('bukti_spbe', 'public');
                $filesBaru[] = $path;
                $sisaKuota--;
            }
        }
        $totalBukti = array_merge($totalLinks, $oldFiles, $filesBaru);

        $dataSimpanCatatan = [
            'nama_catatankriteria' => $cData['nama_catatankriteria'] ?? ($existingCatatan->nama_catatankriteria ?? ''),
            'pencapaian' => $pencapaianInput ?? ($existingCatatan->pencapaian ?? ''),
            'bukti' => json_encode(array_values($totalBukti)),
            'updated_at' => now()
        ];

        if ($existingCatatan) {
            DB::table('catatan_kriteria')->where('id_catatan', $existingCatatan->id_catatan)->update($dataSimpanCatatan);
            $idCatatan = $existingCatatan->id_catatan;
        } else {
            $dataSimpanCatatan['id_indikator'] = $id_indikator;
            $dataSimpanCatatan['tahun'] = $tahun;
            $dataSimpanCatatan['created_at'] = now();
            $idCatatan = DB::table('catatan_kriteria')->insertGetId($dataSimpanCatatan);
        }

$aksi = ''; 
        if ($user->role === 'verifikator') { 
            $aksi = $isEditMode ? 'Verifikator mengupdate nilai' : 'Verifikator memberikan nilai'; 
        } 
        elseif ($user->role === 'user') { 
            $aksi = $buktiDiklik ? 'User memberikan nilai dan bukti' : 'User memberikan nilai'; 
        }
        elseif ($user->role === 'p2') { 
            $aksi = 'P2 menetapkan target nilai'; 
        }
        elseif ($user->role === 'admin') {
            $aksi = 'Admin melakukan penilaian eksternal'; 
        }
        if ($user->role !== 'admin' && !empty($aksi)) {
            DB::table('catatan_logs')->insert([
                'id_catatan'   => $idCatatan, 
                'id_indikator' => $id_indikator, 
                'user_id'      => $user->id,
                'name'         => $user->name, 
                'role'         => $user->role, 
                'tahun'        => $tahun,
                'aksi'         => $aksi,
                'created_at'   => now()
            ]);
        }

        DB::commit();
        return response()->json(['message' => 'Berhasil! Data tersimpan sesuai role.'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
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