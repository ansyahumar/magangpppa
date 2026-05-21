<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use App\Helpers\PenilaianHelper;

class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $getBaseQuery = function($modul) {
            return DB::table('penilaian_kriteria')
                ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
                ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
                ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
                ->where('domain.modul', $modul)
                ->where('penilaian_kriteria.status', 'final')
                ->whereNotNull('penilaian_kriteria.nilai_asesor_internal')
                ->where('penilaian_kriteria.nilai_asesor_internal', '>', 0)
                ->select('penilaian_kriteria.tahun')
                ->distinct()
                ->orderBy('penilaian_kriteria.tahun', 'desc')
                ->pluck('tahun');
        };

        $tahunSPBE = $getBaseQuery('spbe');
        $tahunPEMDI = $getBaseQuery('pemdi');

        return view('verifikator.dashboard', compact('tahunSPBE', 'tahunPEMDI'));
    }

    public function listPenilaian($tahun, $modul)
    {
        $cekAsesor = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('penilaian_kriteria.tahun', $tahun)
            ->where('domain.modul', $modul)
            ->whereNotNull('nilai_asesor_internal')
            ->where('penilaian_kriteria.status', 'final')
            ->exists();

        if (!$cekAsesor) {
            return redirect()->route('verifikator.dashboard')
                ->with('error', "Data penilaian mandiri untuk modul " . strtoupper($modul) . " tahun $tahun belum tersedia.");
        }

        $domains = Domain::where('tahun', $tahun)
            ->where('modul', $modul)
            ->with(['aspek' => function($q) use ($tahun) {
                $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
            }, 'aspek.indikator' => function($q) use ($tahun) {
                $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
            }])
            ->orderBy('urutan', 'asc')
            ->get();
        
        $statusPenilaian = DB::table('penilaian_kriteria')
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('penilaian_kriteria.tahun', $tahun)
            ->where('domain.modul', $modul)
            ->value('status_vrifU') ?? 'draft'; 

        $draft = DB::table('penilaian_kriteria')
            ->select('penilaian_kriteria.id_indikator', DB::raw('MAX(nilai_verifikator_internal) as nilai_verifikator_internal'))
            ->join('indikator', 'penilaian_kriteria.id_indikator', '=', 'indikator.id_indikator')
            ->join('aspek', 'indikator.id_aspek', '=', 'aspek.id_aspek')
            ->join('domain', 'aspek.id_domain', '=', 'domain.id_domain')
            ->where('penilaian_kriteria.tahun', $tahun)
            ->where('domain.modul', $modul)
            ->groupBy('penilaian_kriteria.id_indikator')
            ->get()
            ->keyBy('id_indikator');

        return view('verifikator.form_verifikasi', compact('domains', 'tahun', 'draft', 'statusPenilaian', 'modul'));
    }

   public function finalisasi_verifikator(Request $request)
{
    $tahun = $request->tahun;
    $modul = $request->modul; 
    
    try {
        DB::beginTransaction();
        DB::table('penilaian_kriteria')
            ->where('tahun', $tahun)
            ->where('modul', $modul)
            ->update(['status_vrifU' => 'final']); 
        PenilaianHelper::calculateVerifikator($tahun, $modul);
        DB::commit();
        return redirect()->back()->with('success', "Modul " . strtoupper($modul) . " Berhasil Difinalisasi dan Dihitung.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
    }
}
    public function storeVerifikasi(Request $request)
{
    try {
        $id_indikator = $request->id_indikator;
        $tahun = $request->tahun;
        $modul = $request->modul;
        $kriteria = DB::table('penilaian_kriteria')
            ->where('id_indikator', $id_indikator)
            ->where('tahun', $tahun)
            ->where('modul', $modul)
            ->first();

        if (!$kriteria) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Data penilaian tidak ditemukan.'
            ], 404);
        }

        if (is_null($kriteria->nilai_asesor_internal) || $kriteria->nilai_asesor_internal == 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal: Verifikator belum bisa memberi nilai karena User belum mengisi penilaian.'
            ], 400);
        }

        $kriteriaData = json_decode($request->kriteria, true);
        $nilaiBaru = $kriteriaData[0]['nilai_verifikator_internal'];

        DB::table('penilaian_kriteria')
            ->where('id_penilaian', $kriteria->id_penilaian)
            ->update([
                'nilai_verifikator_internal' => $nilaiBaru,
                'status_vrifU' => 'verifed',
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Nilai verifikasi berhasil disimpan.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error', 
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ], 500);
    }
}
}