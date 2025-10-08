<?php

namespace App\Http\Controllers;

use App\Models\Jalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JalanController extends Controller
{
    // Ambil semua data jalan
    public function index()
    {
        $data = Jalan::select(
                'id_jalan',
                'kode_unik',
                'kondisi',
                'lebar',
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
                'created_at'
            )
            ->orderBy('id_jalan', 'asc') // urutkan dari terkecil
            ->get();

        return response()->json($data);
    }

    // Ambil data berdasarkan tanggal
    public function getByDate(Request $request)
    {
        $tanggal = $request->query('tanggal');

        if (!$tanggal) {
            return response()->json([
                'message' => 'Parameter tanggal wajib diisi (format: YYYY-MM-DD)'
            ], 400);
        }

        $data = Jalan::select(
                'id_jalan',
                'kode_unik',
                'kondisi',
                'lebar',
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
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

    // Simpan data jalan baru
    public function store(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string|max:50',
            'kondisi'   => 'required|string|max:255',
            'lebar'     => 'required|numeric',
            'lokasi'    => 'nullable|string',
            'created_at'=> 'nullable|date'
        ]);

        $jalan = new Jalan();
        $jalan->kode_unik = $request->kode_unik;
        $jalan->kondisi   = $request->kondisi;
        $jalan->lebar     = $request->lebar;

        // Proses lokasi
        if ($request->filled('lokasi')) {
            // Jika formatnya hex (WKB)
            if (ctype_xdigit($request->lokasi)) {
                $jalan->lokasi = DB::raw("ST_SetSRID(ST_GeomFromWKB(decode('{$request->lokasi}', 'hex')), 4326)");
            } else {
                // Anggap format WKT
                $jalan->lokasi = DB::raw("ST_GeomFromText('{$request->lokasi}', 4326)");
            }
        } else {
            // Ambil dari data lama
            $lokasiLama = DB::table('jalan')
                ->where('kode_unik', $request->kode_unik)
                ->orderByDesc('created_at')
                ->value(DB::raw('ST_AsText(lokasi)'));

            if ($lokasiLama) {
                $jalan->lokasi = DB::raw("ST_GeomFromText('{$lokasiLama}', 4326)");
            } else {
                return response()->json([
                    'message' => 'Lokasi wajib diisi, atau harus sudah ada data lama dengan kode_unik ini.'
                ], 422);
            }
        }

        if ($request->filled('created_at')) {
            $jalan->created_at = $request->created_at;
        }

        $jalan->save();

        // Ambil ulang data lengkap
        $data = Jalan::select(
            'id_jalan',
            'kode_unik',
            'kondisi',
            'lebar',
            DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
            DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
            'created_at'
        )->find($jalan->id_jalan);

        return response()->json([
            'message' => 'Data jalan berhasil disimpan',
            'data' => $data
        ], 201);
    }

    // Update data jalan (partial update)
    public function update(Request $request, $id)
    {
        $jalan = Jalan::findOrFail($id);

        // Validasi optional (semua tidak required)
        $request->validate([
            'kode_unik' => 'sometimes|string|max:50',
            'kondisi'   => 'sometimes|string|max:255',
            'lebar'     => 'sometimes|numeric',
            'lokasi'    => 'sometimes|string',
            'created_at'=> 'sometimes|date'
        ]);

        // Update hanya field yang dikirim
        if ($request->filled('kode_unik')) {
            $jalan->kode_unik = $request->kode_unik;
        }
        if ($request->filled('kondisi')) {
            $jalan->kondisi = $request->kondisi;
        }
        if ($request->filled('lebar')) {
            $jalan->lebar = $request->lebar;
        }
        if ($request->filled('lokasi')) {
            if (ctype_xdigit($request->lokasi)) {
                $jalan->lokasi = DB::raw("ST_SetSRID(ST_GeomFromWKB(decode('{$request->lokasi}', 'hex')), 4326)");
            } else {
                $jalan->lokasi = DB::raw("ST_GeomFromText('{$request->lokasi}', 4326)");
            }
        }
        if ($request->filled('created_at')) {
            $jalan->created_at = $request->created_at;
        }

        $jalan->save();

        $data = Jalan::select(
            'id_jalan',
            'kode_unik',
            'kondisi',
            'lebar',
            DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi_wkb"),
            DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
            'created_at'
        )->find($jalan->id_jalan);

        return response()->json([
            'message' => 'Data jalan berhasil diupdate',
            'data' => $data
        ]);
    }

    // Hapus data jalan
    public function destroy($id)
    {
        $jalan = Jalan::findOrFail($id);
        $jalan->delete();

        return response()->json([
            'message' => 'Data jalan berhasil dihapus'
        ]);
    }
}
