<?php

namespace App\Http\Controllers;

use App\Models\Irigasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IrigasiController extends Controller
{
    // Ambil semua data
    public function index()
    {
        $data = DB::table('irigasi')
            ->select(
                'id_irigasi',
                'kondisi',
                'sumber',
                'luas',
                'kode_unik',
                'created_at',
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                DB::raw("ST_AsGeoJSON(ST_Transform(lokasi, 4326), 9, 1)::json as lokasi_geojson")
            )
            ->orderBy('id_irigasi', 'asc')
            ->get();

        return response()->json($data);
    }

    // Ambil data versi terakhir per kode_unik sebelum/sama tanggal tertentu
    public function getByDate(Request $request)
    {
        $tanggal = $request->query('tanggal'); // format YYYY-MM-DD

        if (!$tanggal) {
            return response()->json([
                'message' => 'Parameter tanggal wajib diisi (format: YYYY-MM-DD)'
            ], 400);
        }

        $data = Irigasi::select(
                'id_irigasi',
                'kode_unik',
                'kondisi',
                'sumber',
                'luas',
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                DB::raw("ST_AsGeoJSON(ST_Transform(lokasi, 4326), 9, 1)::json as lokasi_geojson"),
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

    // Simpan data baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'lokasi'    => 'required|string', // HEX WKB
                'kondisi'   => 'nullable|string|max:50',
                'sumber'    => 'nullable|string|max:100',
                'luas'      => 'nullable|numeric',
                'kode_unik' => 'nullable|string|max:20'
            ]);

            if (empty($validated['kode_unik'])) {
                $validated['kode_unik'] = 'IRG-' . strtoupper(Str::random(6));
            }

            // Insert data manual biar bisa binding lokasi
            $id = DB::selectOne("
                INSERT INTO irigasi (kondisi, sumber, luas, kode_unik, lokasi, created_at)
                VALUES (?, ?, ?, ?, ST_SetSRID(ST_GeomFromWKB(decode(?, 'hex')), 32748), ?)
                RETURNING id_irigasi
            ", [
                $validated['kondisi'] ?? null,
                $validated['sumber'] ?? null,
                $validated['luas'] ?? null,
                $validated['kode_unik'],
                $validated['lokasi'],
                now()
            ])->id_irigasi;

            // Ambil kembali data yang baru dimasukkan
            $data = DB::table('irigasi')
                ->select(
                    'id_irigasi',
                    'kondisi',
                    'sumber',
                    'luas',
                    'kode_unik',
                    DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                    DB::raw("ST_AsGeoJSON(ST_Transform(lokasi, 4326), 9, 1)::json as lokasi_geojson"),
                    'created_at'
                )
                ->where('id_irigasi', $id)
                ->first();

            return response()->json($data, 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Tampilkan detail by ID
    public function show($id)
    {
        $irigasi = DB::table('irigasi')
            ->select(
                'id_irigasi',
                'kondisi',
                'sumber',
                'luas',
                'kode_unik',
                'created_at',
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                DB::raw("ST_AsGeoJSON(ST_Transform(lokasi, 4326), 9, 1)::json as lokasi_geojson")
            )
            ->where('id_irigasi', $id)
            ->first();

        return response()->json($irigasi);
    }

    // Update data
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'lokasi'    => 'sometimes|string',
                'kondisi'   => 'nullable|string|max:50',
                'sumber'    => 'nullable|string|max:100',
                'luas'      => 'nullable|numeric',
                'kode_unik' => 'nullable|string|max:20'
            ]);

            // ambil data lama
            $existing = DB::table('irigasi')->where('id_irigasi', $id)->first();
            if (!$existing) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            // merge: kalau ada input baru, pakai itu. kalau tidak, pakai data lama.
            $data = [
                'kondisi'   => $validated['kondisi']   ?? $existing->kondisi,
                'sumber'    => $validated['sumber']    ?? $existing->sumber,
                'luas'      => $validated['luas']      ?? $existing->luas,
                'kode_unik' => $validated['kode_unik'] ?? $existing->kode_unik,
            ];

            if (!empty($validated['lokasi'])) {
                $data['lokasi'] = DB::raw("
                    ST_SetSRID(
                        ST_GeomFromWKB(
                            decode('{$validated['lokasi']}', 'hex')
                        ), 32748
                    )
                ");
            } else {
                // kalau tidak dikirim lokasi, biarkan lokasi lama
                $data['lokasi'] = DB::raw("lokasi");
            }

            DB::table('irigasi')->where('id_irigasi', $id)->update($data);

            return response()->json(['message' => 'Data irigasi berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengupdate data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Hapus data
    public function destroy($id)
    {
        Irigasi::findOrFail($id)->delete();
        return response()->json(['message' => 'Data irigasi berhasil dihapus.']);
    }
}
