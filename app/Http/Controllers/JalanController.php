<?php

namespace App\Http\Controllers;

use App\Models\Jalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JalanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jalan::with('uploadPeta');

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

        // ✅ Tambahkan urutan berdasarkan ID
        $jalanList = $query->orderBy('id_jalan')->get();

        foreach ($jalanList as $jalan) {
            $raw = DB::table('jalan')
                ->where('id_jalan', $jalan->id_jalan)
                ->selectRaw('ST_AsGeoJSON(lokasi) as geojson')
                ->first();

            $jalan->lokasi_geojson = json_decode($raw->geojson);
        }

        return response()->json($jalanList);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'lokasi' => 'required', // GeoJSON
            'kondisi' => 'nullable|string',
            'lebar' => 'nullable|integer',
            'id_peta' => 'required|exists:upload_peta,id_peta'
        ]);

        $jalan = new Jalan();
        $jalan->kondisi = $validated['kondisi'] ?? null;
        $jalan->lebar = $validated['lebar'] ?? null;
        $jalan->id_peta = $validated['id_peta'];

        $geojson = json_encode([
            "type" => "MultiLineString",
            "coordinates" => $validated['lokasi']
        ]);

        $jalan->lokasi = DB::raw("ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON('$geojson'), 32748), 4326)");
        $jalan->save();

        $jalan->refresh(); // <=== ini kunci agar lokasi dapat value beneran

        $geo = DB::table('jalan')
            ->where('id_jalan', $jalan->id_jalan)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson, lokasi')
            ->first();

        $jalan->lokasi_geojson = json_decode($geo->geojson);
        $jalan->lokasi = $geo->lokasi;

        return response()->json($jalan, 201);
    }


    public function show($id)
    {
        $jalan = Jalan::with('uploadPeta')->findOrFail($id);

        $geo = DB::table('jalan')
            ->where('id_jalan', $jalan->id_jalan)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson')
            ->first();

        $jalan->lokasi_geojson = json_decode($geo->geojson);

        return response()->json($jalan);
    }

    public function update(Request $request, $id)
    {
        $jalan = Jalan::findOrFail($id);

        $validated = $request->validate([
            'lokasi' => 'sometimes',
            'kondisi' => 'nullable|string',
            'lebar' => 'nullable|integer',
            'id_peta' => 'nullable|exists:upload_peta,id_peta'
        ]);

        if (isset($validated['lokasi'])) {
            $geojson = json_encode([
                'type' => 'MultiLineString',
                'coordinates' => $validated['lokasi']
            ]);
            $jalan->lokasi = DB::raw("ST_SetSRID(ST_GeomFromGeoJSON('$geojson'), 4326)");
            unset($validated['lokasi']);
        }

        $jalan->fill($validated);
        $jalan->save();

        $geo = DB::table('jalan')
            ->where('id_jalan', $jalan->id_jalan)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson')
            ->first();

        $jalan->lokasi_geojson = json_decode($geo->geojson);

        return response()->json($jalan);
    }

    public function destroy($id)
    {
        $jalan = Jalan::findOrFail($id);
        $jalan->delete();
        return response()->json(['message' => 'Data jalan dihapus.']);
    }
}
