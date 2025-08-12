<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlokController extends Controller
{
    // Ambil semua data blok dengan relasi peta
    public function index(Request $request)
    {
        $query = Blok::with(['uploadPeta', 'pekerja']);

        if ($request->has('bulan') || $request->has('tahun')) {
            $query->whereHas('uploadPeta', function ($q) use ($request) {
                if ($request->filled('bulan')) {
                    $q->whereMonth('tanggal_upload', $request->bulan);
                }
                if ($request->filled('tahun')) {
                    $q->whereYear('tanggal_upload', $request->tahun);
                }
            });
        }

        $blokList = $query->orderBy('id_blok')->get();

        foreach ($blokList as $blok) {
            $geojson = DB::table('blok')
                ->where('id_blok', $blok->id_blok)
                ->value(DB::raw('ST_AsGeoJSON(lokasi)'));

            $blok->lokasi_geojson = json_decode($geojson);

            // Tambahkan umur dan kategori dari accessor model
            $blok->umur_pohon = $blok->umur_pohon;
            $blok->kategori_pohon = $blok->kategori_pohon;
        }

        return response()->json($blokList);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_blok' => 'required|string',
            'lokasi' => 'required',
            'waktu_tanam' => 'nullable|date',
            'waktu_panen' => 'nullable|date',
            'id_peta' => 'required|exists:upload_peta,id_peta',
            'id_pekerja' => 'array',
            'id_pekerja.*' => 'exists:pekerja,id_pekerja'
        ]);

        $blokData = $validated;
        unset($blokData['id_pekerja']);

        $blok = new Blok();
        $blok->nama_blok = $blokData['nama_blok'];
        $blok->waktu_tanam = $blokData['waktu_tanam'];
        $blok->waktu_panen = $blokData['waktu_panen'];
        $blok->id_peta = $blokData['id_peta'];
        $blok->lokasi = DB::raw("ST_GeomFromGeoJSON('" . json_encode($blokData['lokasi']) . "')");
        $blok->save();

        if ($request->has('id_pekerja')) {
            $blok->pekerja()->attach($validated['id_pekerja']);
        }

        $blok->load('pekerja');

        $geojson = DB::table('blok')
            ->where('id_blok', $blok->id_blok)
            ->value(DB::raw('ST_AsGeoJSON(lokasi)'));

        $blok->lokasi = json_decode($geojson);

        return response()->json($blok, 201);
    }

    public function show($id)
    {
        $blok = Blok::with(['uploadPeta', 'pekerja'])->findOrFail($id);

        $geojson = DB::table('blok')
            ->where('id_blok', $blok->id_blok)
            ->value(DB::raw('ST_AsGeoJSON(lokasi)'));

        $blok->lokasi_geojson = json_decode($geojson);

        // Tambahkan umur dan kategori dari accessor model
        $blok->umur_pohon = $blok->umur_pohon;
        $blok->kategori_pohon = $blok->kategori_pohon;

        return response()->json($blok);
    }

    public function update(Request $request, $id)
    {
        $blok = Blok::findOrFail($id);

        $validated = $request->validate([
            'nama_blok' => 'string',
            'lokasi' => '',
            'waktu_tanam' => 'nullable|date',
            'waktu_panen' => 'nullable|date',
            'id_peta' => 'exists:upload_peta,id_peta',
            'id_pekerja' => 'array',
            'id_pekerja.*' => 'exists:pekerja,id_pekerja'
        ]);

        if (isset($validated['lokasi'])) {
            $blok->lokasi = DB::raw("ST_GeomFromGeoJSON('" . json_encode($validated['lokasi']) . "')");
        }

        $blok->fill($validated)->save();

        if ($request->has('id_pekerja')) {
            $blok->pekerja()->sync($validated['id_pekerja']);
        }

        $blok->load('pekerja');

        $geojson = DB::table('blok')
            ->where('id_blok', $blok->id_blok)
            ->value(DB::raw('ST_AsGeoJSON(lokasi)'));

        $blok->lokasi = json_decode($geojson);

        return response()->json($blok);
    }

    public function destroy($id)
    {
        $blok = Blok::findOrFail($id);
        $blok->delete();
        return response()->json(['message' => 'Data blok dihapus.']);
    }
}
