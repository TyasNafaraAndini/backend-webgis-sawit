<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $query = Alat::with(['blok', 'pekerja']);

        if ($request->has('id_blok')) {
            $query->where('id_blok', $request->id_blok);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_alat' => 'required|string|max:100',
            'id_pekerja' => 'nullable|array',
            'id_pekerja.*' => 'exists:pekerja,id_pekerja',
            'id_blok' => 'required|exists:blok,id_blok',
            'penggunaan' => 'nullable|string',
            'tanggal' => 'nullable|date',
        ]);

        $alat = Alat::create([
            'nama_alat' => $validated['nama_alat'],
            'id_blok' => $validated['id_blok'],
            'penggunaan' => $validated['penggunaan'] ?? null,
            'tanggal' => $validated['tanggal'] ?? null,
        ]);

        if (!empty($validated['id_pekerja'])) {
            $alat->pekerja()->sync($validated['id_pekerja']);
        }

        return response()->json($alat->load('pekerja'), 201);
    }

    public function show($id)
    {
        $alat = Alat::with(['blok', 'pekerja'])->findOrFail($id);
        return response()->json($alat);
    }

    public function update(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $validated = $request->validate([
            'nama_alat' => 'string|max:100',
            'id_blok' => 'exists:blok,id_blok',
            'penggunaan' => 'nullable|string',
            'tanggal' => 'nullable|date',
            'id_pekerja' => 'nullable|array',
            'id_pekerja.*' => 'exists:pekerja,id_pekerja',
        ]);

        $alat->update($validated);

        if ($request->has('id_pekerja')) {
            $alat->pekerja()->sync($validated['id_pekerja']);
        }

        return response()->json($alat->load('pekerja'));
    }

    public function destroy($id)
    {
        $alat = Alat::findOrFail($id);
        $alat->pekerja()->detach(); // lepas relasi dulu
        $alat->delete();

        return response()->json(['message' => 'Data alat berhasil dihapus.']);
    }
}
