<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index()
    {
        return response()->json(Alat::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_alat' => 'required|string|max:100',
            'penggunaan' => 'nullable|string',
        ]);

        $alat = Alat::create($validated);

        return response()->json($alat, 201);
    }

    public function show($id)
    {
        $alat = Alat::findOrFail($id);
        return response()->json($alat);
    }

    public function update(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $validated = $request->validate([
            'nama_alat' => 'sometimes|required|string|max:100',
            'penggunaan' => 'nullable|string',
        ]);

        $alat->update($validated);

        return response()->json($alat);
    }

    public function destroy($id)
    {
        $alat = Alat::findOrFail($id);
        $alat->delete();

        return response()->json(['message' => 'Data alat berhasil dihapus.']);
    }
}
