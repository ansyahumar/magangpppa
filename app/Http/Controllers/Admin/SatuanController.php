<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{
    public function index()
    {
        $data = DB::table('satuan')->paginate(10);
        return view('admin.users.satuan', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate(['satuan' => 'required']);

        DB::table('satuan')->insert([
            'satuan'       => $request->satuan,
            'unit'         => $request->unit,
            'no_indikator' => $request->no_indikator,
        ]);

        return redirect()->back()->with('success', 'Data berhasil ditambah');
    }

   public function update(Request $request, $id)
{
    $request->validate(['satuan' => 'required']);

    \DB::table('satuan')->where('id_satuan', $id)->update([
        'satuan'       => $request->satuan,
        'unit'         => $request->unit,
        'no_indikator' => $request->no_indikator,
    ]);

    return redirect()->route('admin.satuan.index')->with('success', 'Data Berhasil Diperbarui');
}
public function destroy($id)
{
    \DB::table('satuan')->where('id_satuan', $id)->delete();
    return redirect()->back()->with('success', 'Data berhasil dihapus');
}
}