<?php

namespace App\Http\Controllers;

use App\Models\Transportasi;
use Illuminate\Http\Request;

class TransportasiController extends Controller
{
    public function index()
    {
        return response()->json(Transportasi::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_transportasi' => 'required|string|max:50',
            'kapasitas' => 'required|integer',
        ]);

        $data = Transportasi::create($validated);
        return response()->json($data, 201);
    }

    public function show($id)
    {
        $data = Transportasi::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $data = Transportasi::findOrFail($id);

        $validated = $request->validate([
            'jenis_transportasi' => 'string|max:50',
            'kapasitas' => 'integer',
        ]);

        $data->update($validated);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = Transportasi::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Data transportasi dihapus.']);
    }
}
