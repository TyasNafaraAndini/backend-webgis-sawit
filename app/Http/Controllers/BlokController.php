<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlokController extends Controller
{
    // Ambil semua data blok
    public function index()
    {
        $data = Blok::with('pekerja')
            ->select(
                'id_blok',
                'nama_blok',
                'kode_unik',
                'waktu_tanam',
                'waktu_panen',
                'created_at',
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi") // WKB hex
            )
            ->orderBy('id_blok', 'asc')
            ->get();

        return response()->json($data);
    }

    // Ambil data versi terakhir per kode_unik sebelum/sama dengan tanggal tertentu
    public function getByDate(Request $request)
    {
        $tanggal = $request->query('tanggal'); // format: YYYY-MM-DD

        if (!$tanggal) {
            return response()->json([
                'message' => 'Parameter tanggal wajib diisi (format: YYYY-MM-DD)'
            ], 400);
        }

        $data = Blok::with('pekerja')
            ->select(
                'id_blok',
                'nama_blok',
                'kode_unik',
                'waktu_tanam',
                'waktu_panen',
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi"),
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

    // Tambah data blok (bisa versi baru dari kode_unik lama, ambil lokasi otomatis)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kode_unik'     => 'required|string|max:50',
            'nama_blok'     => 'required|string|max:255',
            'waktu_tanam'   => 'required|date',
            'waktu_panen'   => 'required|date',
            'lokasi'        => 'required|string', // WKB hex
            'id_pekerja'    => 'array',
            'id_pekerja.*'  => 'exists:pekerja,id_pekerja'
        ]);
        // Simpan data blok
        $blok = Blok::create([
            'kode_unik'    => $validatedData['kode_unik'],
            'nama_blok'    => $validatedData['nama_blok'],
            'waktu_tanam'  => $validatedData['waktu_tanam'],
            'waktu_panen'  => $validatedData['waktu_panen'],
            'lokasi'       => DB::raw("ST_SetSRID(ST_GeomFromWKB(decode('{$validatedData['lokasi']}', 'hex')), 4326)"),
        ]);
        // Simpan relasi pekerja
        if (!empty($validatedData['id_pekerja'])) {
            $blok->pekerja()->sync($validatedData['id_pekerja']);
        }
        // Ambil kembali data blok dengan pekerja + lokasi
        $blok = Blok::with('pekerja')
            ->select(
                'id_blok',
                'nama_blok',
                'kode_unik',
                'waktu_tanam',
                'waktu_panen',
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi"),
                'created_at'
            )
            ->find($blok->id_blok);
        return response()->json($blok, 201);
    }

    // Detail blok
    public function show($id)
    {
        $blok = Blok::with('pekerja')
            ->select(
                'id_blok',
                'nama_blok',
                'kode_unik',
                'waktu_tanam',
                'waktu_panen',
                DB::raw("ST_AsGeoJSON(lokasi)::json as lokasi_geojson"),
                DB::raw("encode(ST_AsBinary(lokasi), 'hex') as lokasi"),
                'created_at'
            )
            ->where('id_blok', $id)
            ->first();

        return response()->json($blok);
    }

    // Update blok (partial update)
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_blok'    => 'nullable|string|max:100',
                'lokasi'       => 'nullable|string', // WKB hex
                'waktu_tanam'  => 'nullable|date',
                'waktu_panen'  => 'nullable|date',
                'kode_unik'    => 'nullable|string|max:20',
                'id_pekerja'   => 'array',
                'id_pekerja.*' => 'exists:pekerja,id_pekerja'
            ]);
            $data = [];
            if (isset($validated['nama_blok'])) {
                $data['nama_blok'] = $validated['nama_blok'];
            }
            if (isset($validated['waktu_tanam'])) {
                $data['waktu_tanam'] = $validated['waktu_tanam'];
            }
            if (isset($validated['waktu_panen'])) {
                $data['waktu_panen'] = $validated['waktu_panen'];
            }
            if (isset($validated['kode_unik'])) {
                $data['kode_unik'] = $validated['kode_unik'];
            }
            if (!empty($validated['lokasi'])) {
                $data['lokasi'] = DB::raw("ST_SetSRID(ST_GeomFromWKB(decode('{$validated['lokasi']}', 'hex')), 4326)");
            }

            if (!empty($data)) {
                DB::table('blok')->where('id_blok', $id)->update($data);
            }

            if (isset($validated['id_pekerja'])) {
                $blok = Blok::find($id);
                if ($blok) {
                    $blok->pekerja()->sync($validated['id_pekerja']);
                }
            }

            return response()->json(['message' => 'Data blok berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengupdate data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Hapus blok
    public function destroy($id)
    {
        Blok::findOrFail($id)->delete();
        return response()->json(['message' => 'Data blok berhasil dihapus.']);
    }
}