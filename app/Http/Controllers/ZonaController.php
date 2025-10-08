<?php

namespace App\Http\Controllers;

use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZonaController extends Controller
{
    // Ambil semua data zona
    public function index()
    {
        $data = DB::table('zona')
            ->select(
                'id_zona',
                'jumlah',
                'nama_zona',
                'kode_unik',
                'created_at',
                DB::raw("encode(ST_AsBinary(lokasi_zona), 'hex') as lokasi_zona_wkb"),
                DB::raw("ST_AsGeoJSON(lokasi_zona)::json as lokasi_zona_geojson")
            )
            ->orderBy('id_zona', 'asc')
            ->get();

        return response()->json($data);
    }

    // Ambil data zona versi terakhir per kode_unik sebelum/sama dengan tanggal tertentu
    public function getByDate(Request $request)
    {
        $tanggal = $request->query('tanggal'); // format: YYYY-MM-DD

        if (!$tanggal) {
            return response()->json([
                'message' => 'Parameter tanggal wajib diisi (format: YYYY-MM-DD)'
            ], 400);
        }

        $data = Zona::select(
                'id_zona',
                'kode_unik',
                'nama_zona',
                'jumlah',
                DB::raw("encode(ST_AsBinary(lokasi_zona), 'hex') as lokasi_zona_wkb"),
                DB::raw("ST_AsGeoJSON(lokasi_zona)::json as lokasi_zona_geojson"),
                'created_at'
            )
            ->whereDate('created_at', '<=', $tanggal)
            ->orderBy('kode_unik')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('kode_unik')
            ->values();

        return response()->json($data);
    }

    // Tambah data zona
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'jumlah' => 'required|integer',
                'nama_zona' => 'required|string|max:255',
                'lokasi_zona_wkb' => 'required|string', // HEX WKB string
                'kode_unik' => 'nullable|string|max:20'
            ]);

            if (empty($validated['kode_unik'])) {
                $validated['kode_unik'] = 'ZONA-' . strtoupper(Str::random(6));
            }

            DB::table('zona')->insert([
                'jumlah' => $validated['jumlah'],
                'nama_zona' => $validated['nama_zona'],
                'kode_unik' => $validated['kode_unik'],
                'lokasi_zona' => DB::raw("
                    ST_SetSRID(
                        ST_GeomFromWKB(
                            decode('{$validated['lokasi_zona_wkb']}', 'hex')
                        ), 4326
                    )
                "),
                'created_at' => now()
            ]);

            return response()->json(['message' => 'Data zona berhasil disimpan.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Detail zona berdasarkan ID
    public function show($id)
    {
        $zona = DB::table('zona')
            ->select(
                'id_zona',
                'jumlah',
                'nama_zona',
                'kode_unik',
                'created_at',
                DB::raw("encode(ST_AsBinary(lokasi_zona), 'hex') as lokasi_zona_wkb"),
                DB::raw("ST_AsGeoJSON(lokasi_zona)::json as lokasi_zona_geojson")
            )
            ->where('id_zona', $id)
            ->first();

        return response()->json($zona);
    }

    // Update data zona
    public function update(Request $request, $id)
    {
        try {
            $zonaLama = DB::table('zona')->where('id_zona', $id)->first();
            if (!$zonaLama) {
                return response()->json(['message' => 'Data zona tidak ditemukan.'], 404);
            }

            $validated = $request->validate([
                'jumlah' => 'nullable|integer',
                'nama_zona' => 'nullable|string|max:255',
                'lokasi_zona_wkb' => 'nullable|string', // HEX WKB string
                'kode_unik' => 'nullable|string|max:20'
            ]);

            $data = [
                'jumlah' => $validated['jumlah'] ?? $zonaLama->jumlah,
                'nama_zona' => $validated['nama_zona'] ?? $zonaLama->nama_zona,
                'kode_unik' => $validated['kode_unik'] ?? $zonaLama->kode_unik,
            ];

            if (!empty($validated['lokasi_zona_wkb'])) {
                $data['lokasi_zona'] = DB::raw("
                    ST_SetSRID(
                        ST_GeomFromWKB(
                            decode('{$validated['lokasi_zona_wkb']}', 'hex')
                        ), 4326
                    )
                ");
            } else {
                $data['lokasi_zona'] = DB::raw("ST_SetSRID('{$zonaLama->lokasi_zona}'::geometry, 4326)");
            }

            DB::table('zona')->where('id_zona', $id)->update($data);

            return response()->json(['message' => 'Data zona berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengupdate data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Hapus data zona
    public function destroy($id)
    {
        Zona::findOrFail($id)->delete();
        return response()->json(['message' => 'Data zona berhasil dihapus.']);
    }
}
