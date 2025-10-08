<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PohonController extends Controller
{
    // GET semua pohon
    public function index()
    {
        $data = DB::table('pohon')
            ->select(
                'id_pohon',
                DB::raw("ST_X(lokasi_koordinat)::float as longitude"),
                DB::raw("ST_Y(lokasi_koordinat)::float as latitude"),
                'varietas'
            )
            ->orderBy('id_pohon')
            ->get();

        return response()->json($data);
    }

    // GET detail pohon
    public function show($id)
    {
        $pohon = DB::table('pohon')
            ->select(
                'id_pohon',
                DB::raw("ST_X(lokasi_koordinat)::float as longitude"),
                DB::raw("ST_Y(lokasi_koordinat)::float as latitude"),
                'varietas'
            )
            ->where('id_pohon', $id)
            ->first();

        if (!$pohon) {
            return response()->json(['message' => 'Pohon tidak ditemukan'], 404);
        }

        return response()->json($pohon);
    }

    // POST tambah pohon
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'varietas' => 'nullable|string|max:100',
        ]);

        $result = DB::selectOne("
            INSERT INTO pohon (lokasi_koordinat, varietas)
            VALUES (ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)
            RETURNING id_pohon
        ", [$request->longitude, $request->latitude, $request->varietas]);

        return response()->json([
            'id_pohon' => $result->id_pohon,
            'longitude' => (float) $request->longitude,
            'latitude' => (float) $request->latitude,
            'varietas' => $request->varietas,
        ], 201);
    }

    // PUT update pohon
    public function update(Request $request, $id)
    {
        $pohon = DB::table('pohon')->where('id_pohon', $id)->first();
        if (!$pohon) {
            return response()->json(['message' => 'Pohon tidak ditemukan'], 404);
        }

        if ($request->filled('latitude') && $request->filled('longitude') && $request->has('varietas')) {
            // Update lokasi + varietas
            DB::update("
                UPDATE pohon
                SET lokasi_koordinat = ST_SetSRID(ST_MakePoint(?, ?), 4326),
                    varietas = ?
                WHERE id_pohon = ?
            ", [$request->longitude, $request->latitude, $request->varietas, $id]);
        } elseif ($request->filled('latitude') && $request->filled('longitude')) {
            // Update hanya lokasi
            DB::update("
                UPDATE pohon
                SET lokasi_koordinat = ST_SetSRID(ST_MakePoint(?, ?), 4326)
                WHERE id_pohon = ?
            ", [$request->longitude, $request->latitude, $id]);
        } elseif ($request->has('varietas')) {
            // Update hanya varietas
            DB::update("
                UPDATE pohon
                SET varietas = ?
                WHERE id_pohon = ?
            ", [$request->varietas, $id]);
        }

        $updated = DB::table('pohon')
            ->select(
                'id_pohon',
                DB::raw("ST_X(lokasi_koordinat)::float as longitude"),
                DB::raw("ST_Y(lokasi_koordinat)::float as latitude"),
                'varietas'
            )
            ->where('id_pohon', $id)
            ->first();

        return response()->json($updated);
    }

    // DELETE hapus pohon
    public function destroy($id)
    {
        $pohon = DB::table('pohon')->where('id_pohon', $id)->first();
        if (!$pohon) {
            return response()->json(['message' => 'Pohon tidak ditemukan'], 404);
        }

        DB::table('pohon')->where('id_pohon', $id)->delete();

        return response()->json(['message' => 'Pohon berhasil dihapus']);
    }
}
