<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class IndikatorController extends Controller
{
    public function showPanduan($id_indikator, $tipe)
{
    $data = DB::table('penjelasan_indikator')
        ->where('id_indikator', $id_indikator)
        ->first();

   $indikator = DB::table('indikator')->where('id_indikator', $id_indikator)->first();
    if (!$data) {
        return "Data panduan belum diisi oleh admin.";
    }

    return view('panduan.detail', [
        'data' => $data,
        'nama_indikator' => $indikator->nama ?? 'Indikator',
        'tipe' => $tipe 
    ]);
}
}