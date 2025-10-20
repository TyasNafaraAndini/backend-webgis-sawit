<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PohonController extends Controller
{
    // GET semua pohon (bisa filter by zona)
    public function index(Request $request)
    {
        $query = DB::table('pohon')
            ->select(
                'id_pohon',
                DB::raw("ST_X(lokasi_koordinat)::float as longitude"),
                DB::raw("ST_Y(lokasi_koordinat)::float as latitude"),
                'varietas',
                'zona'
            )
            ->orderBy('id_pohon');

        // Jika ada parameter zona, filter berdasarkan zona
        if ($request->has('zona') && !empty($request->zona)) {
            $query->where('zona', $request->zona);
        }

        $data = $query->get();

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
                'varietas',
                'zona'
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
            'zona' => 'nullable|string'
        ]);

        $result = DB::selectOne("
            INSERT INTO pohon (lokasi_koordinat, varietas, zona)
            VALUES (ST_SetSRID(ST_MakePoint(?, ?), 4326), ?, ?)
            RETURNING id_pohon
        ", [$request->longitude, $request->latitude, $request->varietas, $request->zona]);

        return response()->json([
            'id_pohon' => $result->id_pohon,
            'longitude' => (float) $request->longitude,
            'latitude' => (float) $request->latitude,
            'varietas' => $request->varietas,
            'zona' => $request->zona
        ], 201);
    }

    // PUT update pohon
    public function update(Request $request, $id)
    {
        $pohon = DB::table('pohon')->where('id_pohon', $id)->first();
        if (!$pohon) {
            return response()->json(['message' => 'Pohon tidak ditemukan'], 404);
        }

        // Update field sesuai yang dikirim
        $fields = [];
        $params = [];

        if ($request->filled('longitude') && $request->filled('latitude')) {
            $fields[] = "lokasi_koordinat = ST_SetSRID(ST_MakePoint(?, ?), 4326)";
            $params[] = $request->longitude;
            $params[] = $request->latitude;
        }

        if ($request->has('varietas')) {
            $fields[] = "varietas = ?";
            $params[] = $request->varietas;
        }

        if ($request->has('zona')) {
            $fields[] = "zona = ?";
            $params[] = $request->zona;
        }

        if (!empty($fields)) {
            $sql = "UPDATE pohon SET " . implode(', ', $fields) . " WHERE id_pohon = ?";
            $params[] = $id;
            DB::update($sql, $params);
        }

        $updated = DB::table('pohon')
            ->select(
                'id_pohon',
                DB::raw("ST_X(lokasi_koordinat)::float as longitude"),
                DB::raw("ST_Y(lokasi_koordinat)::float as latitude"),
                'varietas',
                'zona'
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
