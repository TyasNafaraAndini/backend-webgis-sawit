<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pekerja;

class PekerjaController extends Controller
{
    public function index()
    {
        return response()->json(Pekerja::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'umur' => 'required|integer',
            'jenis_kelamin' => 'required|string|max:10',
            'lama_kerja' => 'required|integer',
            'kontak' => 'required|string|max:50',
            'pekerjaan' => 'required|string|max:100',
        ]);

        $pekerja = Pekerja::create($validated);

        return response()->json(['message' => 'Pekerja berhasil ditambahkan', 'data' => $pekerja], 201);
    }

    public function show($id)
    {
        $pekerja = Pekerja::findOrFail($id);
        return response()->json($pekerja);
    }

    public function update(Request $request, $id)
    {
        $pekerja = Pekerja::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:100',
            'umur' => 'sometimes|integer',
            'jenis_kelamin' => 'sometimes|string|max:10',
            'lama_kerja' => 'sometimes|integer',
            'kontak' => 'sometimes|string|max:50',
            'pekerjaan' => 'sometimes|string|max:100',
        ]);

        $pekerja->update($validated);

        return response()->json(['message' => 'Pekerja berhasil diupdate', 'data' => $pekerja]);
    }

    public function destroy($id)
    {
        $pekerja = Pekerja::findOrFail($id);
        $pekerja->delete();

        return response()->json(['message' => 'Pekerja berhasil dihapus']);
    }
}
