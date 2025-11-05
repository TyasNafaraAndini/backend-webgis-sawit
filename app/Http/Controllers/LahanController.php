<?php

namespace App\Http\Controllers;

use App\Models\Lahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LahanController extends Controller
{
   public function index()
    {
        $data = DB::table('lahan')
            ->select(
                'id_lahan',
                'penggunaan_sebelumnya',
                'tahun_perubahan',
                'tahun_jadi_sawit',
                'luas',
                DB::raw("encode(ST_AsBinary(batas), 'hex') as batas"),
                DB::raw("ST_AsGeoJSON(batas)::json as batas_geojson")
            )
            ->orderBy('id_lahan', 'asc') 
            ->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'penggunaan_sebelumnya' => 'nullable|string|max:100',
                'tahun_perubahan' => 'nullable|integer',
                'tahun_jadi_sawit' => 'nullable|integer',
                'luas' => 'nullable|numeric',
                'batas' => 'required|string', // HEX WKB string
            ]);

            DB::table('lahan')->insert([
                'penggunaan_sebelumnya' => $validated['penggunaan_sebelumnya'] ?? null,
                'tahun_perubahan' => $validated['tahun_perubahan'] ?? null,
                'tahun_jadi_sawit' => $validated['tahun_jadi_sawit'] ?? null,
                'luas' => $validated['luas'] ?? null,
                'batas' => DB::raw("
                    ST_SetSRID(
                        ST_GeomFromWKB(
                            decode('{$validated['batas']}', 'hex')
                        ), 4326
                    )
                "),
            ]);

            return response()->json(['message' => 'Data berhasil disimpan.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $lahan = DB::table('lahan')
            ->select(
                'id_lahan',
                'penggunaan_sebelumnya',
                'tahun_perubahan',
                'tahun_jadi_sawit',
                'luas',
                DB::raw("encode(ST_AsBinary(batas), 'hex') as batas"),
                DB::raw("ST_AsGeoJSON(batas)::json as batas_geojson")
            )
            ->where('id_lahan', $id)
            ->first();

        return response()->json($lahan);
    }



    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'penggunaan_sebelumnya' => 'nullable|string|max:100',
                'tahun_perubahan' => 'nullable|integer',
                'tahun_jadi_sawit' => 'nullable|integer',
                'luas' => 'nullable|numeric',
                'batas' => 'sometimes', // HEX WKB string
            ]);

            $data = [
                'penggunaan_sebelumnya' => $validated['penggunaan_sebelumnya'] ?? null,
                'tahun_perubahan' => $validated['tahun_perubahan'] ?? null,
                'tahun_jadi_sawit' => $validated['tahun_jadi_sawit'] ?? null,
                'luas' => $validated['luas'] ?? null,
            ];

            if (!empty($validated['batas'])) {
                $data['batas'] = DB::raw("
                    ST_SetSRID(
                        ST_GeomFromWKB(
                            decode('{$validated['batas']}', 'hex')
                        ), 4326
                    )
                ");
            }

            DB::table('lahan')->where('id_lahan', $id)->update($data);

            return response()->json(['message' => 'Data berhasil diperbarui']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengupdate data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Lahan::findOrFail($id)->delete();
        return response()->json(['message' => 'Data lahan berhasil dihapus.']);
    }
}
