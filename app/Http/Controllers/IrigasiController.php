<?php

namespace App\Http\Controllers;

use App\Models\Irigasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IrigasiController extends Controller
{
   public function index()
    {
        $irigasiList = DB::select("
            SELECT 
                id_irigasi,
                kondisi,
                sumber,
                luas,
                id_peta,
                ST_AsGeoJSON(ST_Transform(ST_GeomFromWKB(lokasi), 4326)) AS lokasi_geojson
            FROM irigasi
            ORDER BY id_irigasi ASC
        ");

        $result = collect($irigasiList)->map(function ($item) {
            return [
                'id_irigasi' => $item->id_irigasi,
                'kondisi' => $item->kondisi,
                'sumber' => $item->sumber,
                'luas' => $item->luas,
                'id_peta' => $item->id_peta,
                'lokasi_geojson' => json_decode($item->lokasi_geojson),
            ];
        });

        return response()->json($result);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'lokasi' => 'required', // GeoJSON
            'kondisi' => 'nullable|string|max:50',
            'sumber' => 'nullable|string|max:100',
            'luas' => 'nullable|numeric',
            'id_peta' => 'required|exists:upload_peta,id_peta'
        ]);

        $irigasi = new Irigasi();
        $irigasi->kondisi = $validated['kondisi'] ?? null;
        $irigasi->sumber = $validated['sumber'] ?? null;
        $irigasi->luas = $validated['luas'] ?? null;
        $irigasi->id_peta = $validated['id_peta'];

        $geojson = json_encode($validated['lokasi']);
        $irigasi->lokasi = DB::raw("ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON('$geojson'), 4326), 32748)");

        $irigasi->save();

        $geo = DB::table('irigasi')
            ->where('id_irigasi', $irigasi->id_irigasi)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson, lokasi')
            ->first();

        $irigasi->lokasi_geojson = json_decode($geo->geojson);
        $irigasi->lokasi = $geo->lokasi; // ✅ WKB hex

        return response()->json($irigasi, 201);
    }

    public function show($id)
    {
        $irigasi = Irigasi::with('uploadPeta')->findOrFail($id);

        $geo = DB::table('irigasi')
            ->where('id_irigasi', $irigasi->id_irigasi)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson, lokasi')
            ->first();

        $irigasi->lokasi_geojson = json_decode($geo->geojson);
        $irigasi->lokasi = $geo->lokasi; // ✅ WKB hex

        return response()->json($irigasi);
    }

    public function update(Request $request, $id)
    {
        $irigasi = Irigasi::findOrFail($id);

        $validated = $request->validate([
            'lokasi' => 'sometimes',
            'kondisi' => 'nullable|string|max:50',
            'sumber' => 'nullable|string|max:100',
            'luas' => 'nullable|numeric',
            'id_peta' => 'nullable|exists:upload_peta,id_peta'
        ]);

        // Tangani lokasi
        if (isset($validated['lokasi'])) {
            $geojson = json_encode($validated['lokasi']);
            $irigasi->lokasi = DB::raw("ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON('$geojson'), 4326), 32748)");
            unset($validated['lokasi']);
        }

        $irigasi->fill($validated);
        $irigasi->save();

        $geo = DB::table('irigasi')
            ->where('id_irigasi', $irigasi->id_irigasi)
            ->selectRaw('ST_AsGeoJSON(lokasi) as geojson, lokasi')
            ->first();

        $irigasi->lokasi_geojson = json_decode($geo->geojson);
        $irigasi->lokasi = $geo->lokasi; // ✅ WKB hex

        return response()->json($irigasi);
    }

    public function destroy($id)
    {
        $irigasi = Irigasi::findOrFail($id);
        $irigasi->delete();
        return response()->json(['message' => 'Data irigasi dihapus.']);
    }
}
