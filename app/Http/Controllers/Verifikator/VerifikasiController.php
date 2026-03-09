<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\PenilaianHelper;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
public function index(Request $request)
{
    $tahunSelesaiAsesor = DB::table('penilaian_kriteria')
        ->whereNotNull('nilai_asesor_internal')
        ->where('nilai_asesor_internal', '>', 0)
        ->select(columns: 'tahun')
        ->distinct()
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    return view('verifikator.dashboard', compact('tahunSelesaiAsesor'));
}

public function listPenilaian($tahun)
{
     $cekAsesor = DB::table('penilaian_kriteria')
        ->where('tahun', $tahun)
        ->whereNotNull('nilai_asesor_internal')
        ->exists();

    if (!$cekAsesor) {
        return redirect()->route('verifikator.verifikasi')
            ->with('error', 'Data penilaian mandiri (Asesor) untuk tahun ' . $tahun . ' belum tersedia.');
    }

    $domains = \App\Models\Domain::where('tahun', $tahun)
        ->with(['aspek' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
        }, 'aspek.indikator' => function($q) use ($tahun) {
            $q->where('tahun', $tahun)->orderBy('urutan', 'asc');
        }])
        ->orderBy('urutan', 'asc')
        ->get();
    
    $draft = DB::table('penilaian_kriteria')
        ->select('id_indikator', DB::raw('MAX(nilai_verifikator_internal) as nilai_verifikator_internal'))
        ->where('tahun', $tahun)
        ->groupBy('id_indikator')
        ->get()
        ->keyBy('id_indikator');

    return view('verifikator.form_verifikasi', compact('domains', 'tahun', 'draft'));
}

}