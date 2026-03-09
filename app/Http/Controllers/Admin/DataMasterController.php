<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use App\Models\Aspek;
use App\Models\Indikator;
use App\Models\Kriteria;
use App\Models\BobotDomain;
use App\Models\BobotAspek;
use App\Models\PenjelasanIndikator;

class DataMasterController extends Controller
{
public function monitoring(Request $request)
{
    $tahun = $request->get('tahun', date('Y'));

    $availableYears = DB::table('domain')->distinct()->pluck('tahun')->toArray(); 
    


$domains = Domain::where('tahun', $tahun)
    ->orderBy('urutan', 'asc')
    ->with(['aspek' => function($q) {
        $q->orderBy('urutan', 'asc');
    }, 'aspek.indikator' => function($q) {
        $q->orderBy('nomor_indikator', 'asc');
    }])
    ->get();

    $draft = DB::table('penilaian_kriteria')
                ->where('tahun', $tahun)
                ->get()
                ->keyBy('id_indikator'); 

    try {
        $finalizedYears = DB::table('penilaian_final')->pluck('tahun')->toArray();
    } catch (\Exception $e) {
        $finalizedYears = []; 
    }

    return view('admin.monitor', compact('availableYears', 'tahun', 'finalizedYears', 'domains', 'draft'));
}
   public function index(Request $request)
{
      $tahunDipilih = $request->input('tahun', date('Y'));
    $availableYears = Domain::distinct()
        ->orderBy('tahun', 'asc')
        ->pluck('tahun');
        if ($availableYears->isEmpty()) {
        $availableYears = collect([(int)$tahunDipilih]);
    }
    $domain = Domain::where('tahun', $tahunDipilih)
        ->with([
            'bobot',
            'aspek' => function($q) use ($tahunDipilih) {
                $q->where('tahun', $tahunDipilih)->orderBy('urutan', 'asc');
            },
            'aspek.bobot',
            'aspek.indikator' => function($q) use ($tahunDipilih) {
                $q->where('tahun', $tahunDipilih)->orderBy('urutan', 'asc');
            },
            'aspek.indikator.kriteria' => function($q) use ($tahunDipilih) {
                 $q->where('tahun', $tahunDipilih);
            },
            'aspek.indikator.penjelasan'
        ])
        ->orderBy('urutan', 'asc')
        ->get();

     $indikators = Indikator::where('tahun', $tahunDipilih)
        ->orderBy('urutan', 'asc')
        ->get();

    $aspek = Aspek::where('tahun', $tahunDipilih)
        ->with(['indikator.kriteria' => function($q) use ($tahunDipilih) {
            $q->where('tahun', $tahunDipilih);
        }])
        ->orderBy('urutan', 'asc')
        ->get();

    return view('admin.master.index', compact('domain', 'indikators', 'aspek','availableYears', 'tahunDipilih'));
}
public function storeDomain(Request $request)
{
    $request->validate([
        'nama_domain' => 'required',
        'tahun'       => 'required|numeric',
        'bobot'       => 'required|numeric'
    ]);

    try {
        DB::beginTransaction();

        $urutanTerakhir = Domain::where('tahun', $request->tahun)->max('urutan') ?? 0;
        $domain = new Domain();
        $domain->nama_domain = $request->nama_domain;
        $domain->tahun       = $request->tahun;
        $domain->urutan      = $urutanTerakhir + 1;
        $domain->save();


        $bobot = new BobotDomain();
        $bobot->id_domain = $domain->id_domain; 
        $bobot->tahun     = $domain->tahun;
        $bobot->bobot     = $request->bobot;
        $bobot->save();

        DB::commit();

        return back()
            ->with('success', 'Domain dan Bobot berhasil ditambahkan.')
            ->with('open_domain', $domain->id_domain);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal simpan: ' . $e->getMessage());
    }
}

public function updateDomain(Request $request, $id)
{
    $request->validate([
        'nama_domain' => 'required',
        'bobot'       => 'required|numeric|min:0|max:100' 
    ]);

    $domain = Domain::findOrFail($id);
    $domain->update(['nama_domain' => $request->nama_domain]);

    BobotDomain::updateOrCreate(
        ['id_domain' => $id],
        [
            'bobot' => $request->bobot,
            'tahun' => $domain->tahun
        ]
    );

    return back()->with('success', 'Domain & Bobot berhasil diperbarui.');
}

public function deleteDomain($id)
{
    try {
        return DB::transaction(function () use ($id) {
            $aspekIds = DB::table('aspek')->where('id_domain', $id)->pluck('id_aspek')->toArray();
            $indikatorIds = [];
            if (!empty($aspekIds)) {
                $indikatorIds = DB::table('indikator')->whereIn('id_aspek', $aspekIds)->pluck('id_indikator')->toArray();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            if (!empty($indikatorIds)) {
                DB::table('penjelasan_indikator')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('catatan_logs')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('catatan_kriteria')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('kriteria')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('indikator')->whereIn('id_aspek', $aspekIds)->delete();
            }

            if (!empty($aspekIds)) {
                DB::table('bobot_aspek')->whereIn('id_aspek', $aspekIds)->delete();
                DB::table('aspek')->where('id_domain', $id)->delete();
            }

            DB::table('bobot_domain')->where('id_domain', $id)->delete();
            DB::table('domain')->where('id_domain', $id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Domain dan Master data berhasil dihapus. Data penilaian tetap aman di database.');
        });
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('error', 'Gagal total: ' . $e->getMessage());
    }
}

public function deleteAspek($id)
{
    try {
        return DB::transaction(function () use ($id) {
            $aspek = DB::table('aspek')->where('id_aspek', $id)->first();
            if (!$aspek) {
                return back()->with('error', 'Data Aspek tidak ditemukan.');
            }

            $indikatorIds = DB::table('indikator')->where('id_aspek', $id)->pluck('id_indikator')->toArray();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            if (!empty($indikatorIds)) {
                DB::table('penjelasan_indikator')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('catatan_logs')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('catatan_kriteria')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('kriteria')->whereIn('id_indikator', $indikatorIds)->delete();
                DB::table('indikator')->where('id_aspek', $id)->delete();
            }

            DB::table('bobot_aspek')->where('id_aspek', $id)->delete();
            DB::table('aspek')->where('id_aspek', $id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('success', 'Aspek dan seluruh data master terkait (Indikator, Kriteria, Logs, dll) berhasil dihapus.');
        });
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('error', 'Gagal menghapus aspek: ' . $e->getMessage());
    }
}

public function deleteIndikator($id)
{
    try {
        return DB::transaction(function () use ($id) {
            $indikator = DB::table('indikator')->where('id_indikator', $id)->first();
            if (!$indikator) {
                return back()->with('error', 'Data Indikator tidak ditemukan.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');            
            DB::table('penjelasan_indikator')->where('id_indikator', $id)->delete();
            DB::table('catatan_logs')->where('id_indikator', $id)->delete();
            DB::table('catatan_kriteria')->whereIn('id_indikator', [$id])->delete();
            DB::table('kriteria')->where('id_indikator', $id)->delete();
            DB::table('indikator')->where('id_indikator', $id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('success', 'Indikator dan data master terkait (Kriteria, Logs, Penjelasan) berhasil dihapus.');
        });
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('error', 'Gagal menghapus indikator: ' . $e->getMessage());
    }
}

   public function deleteKriteria($id)
    {
        Kriteria::findOrFail($id)->delete();
        return back()->with('success', 'Kriteria berhasil dihapus.');
    }
public function storeAspek(Request $request)
{
    $request->validate([
        'id_domain'  => 'required',
        'nama_aspek' => 'required',
        'target'     => 'required|numeric',
        'bobot'      => 'required|numeric'
    ]);

    try {
        DB::beginTransaction();
        $domain = Domain::findOrFail($request->id_domain);
        $aspek = Aspek::create([
            'id_domain'  => $request->id_domain,
            'nama_aspek' => $request->nama_aspek,
            'target'     => $request->target,
            'tahun'      => $domain->tahun,
            'urutan'     => Aspek::where('id_domain', $request->id_domain)->max('urutan') + 1
        ]);

        BobotAspek::create([
            'id_aspek' => $aspek->id_aspek,
            'tahun'    => $domain->tahun,
            'bobot'    => $request->bobot
        ]);

        DB::commit();
        return back()->with('success', 'Aspek dan Bobot berhasil disimpan!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal simpan: ' . $e->getMessage());
    }
}

public function updateAspek(Request $request, $id)
{
    $request->validate([
        'id_domain'  => 'required|exists:domain,id_domain',
        'nama_aspek' => 'required',
        'target'     => 'required|numeric|min:0|max:5',
        'bobot'      => 'required|numeric|min:0|max:100'
    ]);

    $aspek = Aspek::findOrFail($id);
    $domain = Domain::findOrFail($request->id_domain);

    $aspek->update([
        'id_domain'  => $request->id_domain,
        'nama_aspek' => $request->nama_aspek,
        'target'     => $request->target,
        'tahun'      => $domain->tahun
    ]);

    BobotAspek::updateOrCreate(
        ['id_aspek' => $id],
        ['bobot'    => $request->bobot, 'tahun' => $domain->tahun]
    );

    return back()->with('success', 'Aspek & bobot berhasil diperbarui.');
}



public function storeIndikator(Request $request)
{
   $request->validate([
        'id_aspek'        => 'required|exists:aspek,id_aspek',
        'nama_indikator'  => 'required'
    ]);

    $aspek = Aspek::findOrFail($request->id_aspek);

   $dataTerakhir = Indikator::where('id_aspek', $request->id_aspek)
        ->selectRaw('MAX(urutan) as max_urutan, MAX(nomor_indikator) as max_nomor')
        ->first();

    $urutanBaru = ($dataTerakhir->max_urutan ?? 0) + 1;
    $nomorBaru  = ($dataTerakhir->max_nomor ?? 0) + 1;

   $indikator = Indikator::create([
        'id_aspek'        => $request->id_aspek,
        'nomor_indikator' => $nomorBaru, 
        'nama_indikator'  => $request->nama_indikator,
        'tahun'           => $aspek->tahun,
        'urutan'          => $urutanBaru,
        'bobot'           => 1.00
    ]);

    return redirect()
        ->route('master.index', ['tahun' => $aspek->tahun])
        ->with('success', 'Indikator berhasil ditambahkan otomatis.');
}
public function updateIndikator(Request $request, $id)
{
    $request->validate([
        'id_aspek'        => 'required|exists:aspek,id_aspek',
        'nomor_indikator' => 'required|numeric',
        'nama_indikator'  => 'required'
    ]);

    $indikator = Indikator::findOrFail($id);
    $aspek = DB::table('aspek')->where('id_aspek', $request->id_aspek)->first();
    $indikator->update([
        'id_aspek'        => $request->id_aspek,
        'nomor_indikator' => $request->nomor_indikator,
        'nama_indikator'  => $request->nama_indikator,
        'tahun'           => $aspek->tahun ?? $indikator->tahun
    ]);

    return back()->with('success', 'Indikator berhasil diperbarui.');
}

public function storeKriteria(Request $request)
{
    $validated = $request->validate([
        'id_indikator'  => 'required|integer',
        'nama_kriteria' => 'required|string',
        'bobot_nilai'   => 'required|numeric',
    ]);

    $indikator = Indikator::find($request->id_indikator);
    $kriteria = new Kriteria();
    $kriteria->id_indikator  = $request->id_indikator;
    $kriteria->nama_kriteria = $request->nama_kriteria;
    $kriteria->bobot_nilai   = $request->bobot_nilai;
    $kriteria->tahun         = $indikator ? $indikator->tahun : 2024;
    
    if ($kriteria->save()) {
        return back()->with('success', 'Kriteria berhasil masuk ke database!');
    } else {
        return back()->with('error', 'Gagal menyimpan ke database.');
    }
}
public function updateKriteria(Request $request, $id)
{
    $request->validate([
        'nama_kriteria' => 'required',
        'bobot_nilai'   => 'required|numeric',
        'id_indikator'  => 'required|exists:indikator,id_indikator'
    ]);

    $kriteria = Kriteria::findOrFail($id);
    $indikatorTujuan = Indikator::findOrFail($request->id_indikator);

    $kriteria->update([
        'nama_kriteria' => $request->nama_kriteria,
        'bobot_nilai'   => $request->bobot_nilai,
        'id_indikator'  => $request->id_indikator,
        'tahun'         => $indikatorTujuan->tahun,
    ]);

    return back()->with('success', 'Kriteria berhasil diperbarui.');
}

   public function storePenjelasan(Request $request)
{
    $request->validate([
        'id_indikator' => 'required',
        'tahun'        => 'required',
        'penjelasan_kriteria' => 'required',
        'tatacara_penilaian'  => 'required',
    ]);

    try {
        DB::table('penjelasan_indikator')->updateOrInsert(
            ['id_indikator' => $request->id_indikator, 'tahun' => $request->tahun],
            [
                'tatacara_penilaian'  => $request->tatacara_penilaian,
                'penjelasan_kriteria' => $request->penjelasan_kriteria,
                'deskripsi'           => $request->deskripsi,
                'updated_at'          => now(),
            ]
        );

        $indikator = DB::table('indikator')->where('id_indikator', $request->id_indikator)->first();
        $id_aspek  = $indikator->id_aspek;
        $id_domain = DB::table('aspek')->where('id_aspek', $id_aspek)->value('id_domain');

        return back()->with('success', 'Data Berhasil Disimpan')
                     ->with('open_domain', $id_domain) 
                     ->with('open_aspek', $id_aspek);  

    } catch (\Exception $e) {
        return back()->with('error', 'Gagal: ' . $e->getMessage());
    }
}

public function updatePenjelasan(Request $request, $id)
{
   
$idIndikator = $request->id_indikator_tubuh ?: $id;
    $request->validate([
        'penjelasan_kriteria' => 'required',
        'tatacara_penilaian'  => 'required',
        'tahun'               => 'required',
    ]);

    DB::table('penjelasan_indikator')->updateOrInsert(
        [
            'id_indikator' => $idIndikator,
            'tahun'        => $request->tahun, 
        ], 
        [
            'penjelasan_kriteria' => $request->penjelasan_kriteria,
            'tatacara_penilaian'  => $request->tatacara_penilaian,
            'deskripsi'           => $request->deskripsi,
            'updated_at'          => now(),
        ]
    );

    return redirect()->back()->with('success', 'Data Berhasil Diperbarui.');
}
public function deletePenjelasan($id) 
{
     $data = PenjelasanIndikator::findOrFail($id);
    $data->delete();

    return back()->with('success', 'Data penjelasan berhasil dihapus.');
}

     public function updateBobotDomain(Request $request, $id)
    {
        $request->validate(['bobot' => 'required|numeric']);

        BobotDomain::updateOrCreate(
            ['id_domain' => $id],
            ['bobot'     => $request->bobot]
        );

        return back()->with('success', 'Bobot domain berhasil diperbarui.');
    }

    public function updateBobotAspek(Request $request, $id)
    {
        $request->validate(['bobot' => 'required|numeric']);

        BobotAspek::updateOrCreate(
            ['id_aspek' => $id],
            ['bobot'    => $request->bobot]
        );

        return back()->with('success', 'Bobot aspek berhasil diperbarui.');
    }
    public function moveAspek(Request $request) {
    $aspek = Aspek::find($request->id_aspek);
    $aspek->id_domain = $request->id_domain;
    $aspek->save();
    return response()->json(['status' => 'success']);
}

public function moveIndikator(Request $request)
{
    try {
        $id_indikator = $request->id_indikator;
        $id_aspek = $request->id_aspek;

        if (!$id_indikator || !$id_aspek) {
            return response()->json(['error' => 'Data tidak lengkap'], 400);
        }

        $indikator = Indikator::findOrFail($id_indikator);
        $indikator->id_aspek = $id_aspek;
        $indikator->save();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function moveDomain(Request $request)
{
    try {
        $order = $request->order;

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Data urutan kosong'], 400);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                DB::table('domain')
                    ->where('id_domain', $id)
                    ->update([
                        'urutan' => $index + 1,
                    ]);
            }
        });

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

public function copyStructure(Request $request)
{
    $tahunBaru = $request->tahun; 
    $tahunLama = $tahunBaru - 1; 

    $exists = Domain::where('tahun', $tahunBaru)->exists();
    if ($exists) {
        return back()->with('error', 'Data tahun ' . $tahunBaru . ' sudah ada!');
    }

    $oldDomains = Domain::where('tahun', $tahunLama)
        ->with('aspek.indikator.kriteria')
        ->get();

    foreach ($oldDomains as $oldDom) {
        /** @var Domain $oldDom */
        $newDom = $oldDom->replicate(); 
        $newDom->tahun = $tahunBaru;
        $newDom->save();

         $oldBobotDom = DB::table('bobot_domain')->where('id_domain', $oldDom->id_domain)->first();
        if ($oldBobotDom) {
            DB::table('bobot_domain')->insert([
                'id_domain' => $newDom->id_domain,
                'tahun'     => $tahunBaru,
                'bobot'     => $oldBobotDom->bobot
            ]);
        }

        foreach ($oldDom->aspek as $oldAsp) {
             $newAsp = $oldAsp->replicate();
            $newAsp->id_domain = $newDom->id_domain; 
            $newAsp->tahun = $tahunBaru;
            $newAsp->save();

            $oldBobotAsp = DB::table('bobot_aspek')->where('id_aspek', $oldAsp->id_aspek)->first();
            if ($oldBobotAsp) {
                DB::table('bobot_aspek')->insert([
                    'id_aspek' => $newAsp->id_aspek,
                    'tahun'    => $tahunBaru,
                    'bobot'    => $oldBobotAsp->bobot
                ]);
            }

            foreach ($oldAsp->indikator as $oldInd) {
                $newInd = $oldInd->replicate();
                $newInd->id_aspek = $newAsp->id_aspek; 
                $newInd->tahun = $tahunBaru;
                $newInd->save();

                $oldPenjelasan = DB::table('penjelasan_indikator') 
                ->where('id_indikator', $oldInd->id_indikator)
                    ->first();

                if ($oldPenjelasan) {
                    $dataPenjelasan = (array) $oldPenjelasan;
                    
                    unset($dataPenjelasan['id_penjelasan_penulisan']); 
                    
                    $dataPenjelasan['id_indikator'] = $newInd->id_indikator;
                    
                    if (array_key_exists('tahun', $dataPenjelasan)) {
                        $dataPenjelasan['tahun'] = $tahunBaru;
                    }

                    DB::table('penjelasan_indikator')->insert($dataPenjelasan);
                }

                foreach ($oldInd->kriteria as $oldKrit) {
                    $newKrit = $oldKrit->replicate();
                    $newKrit->id_indikator = $newInd->id_indikator; 
                    $newKrit->tahun = $tahunBaru;
                    $newKrit->save();
                }
            }
        }
    }

    return back()->with('success', 'Berhasil menyalin master data, bobot, dan penjelasan ke tahun ' . $tahunBaru);
}
}