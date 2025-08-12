<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadPeta;
use Illuminate\Support\Facades\Storage;

class UploadPetaController extends Controller
{
    public function index()
    {
        return response()->json(UploadPeta::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peta' => 'required|string|max:100',
            'tanggal_upload' => 'required|date',
            'uploader' => 'required|string|max:100',
            'format_file' => 'required|string|max:10',
            'file' => 'required|file|mimes:tif,tiff|max:512000', // max 500MB
        ]);

        $path = $request->file('file')->store('public/peta');

        $upload = UploadPeta::create([
            'nama_peta' => $request->nama_peta,
            'tanggal_upload' => $request->tanggal_upload,
            'uploader' => $request->uploader,
            'format_file' => $request->format_file,
            'link_peta' => asset(Storage::url($path)), // Link lengkap
        ]);

        return response()->json(['message' => 'Peta berhasil diupload', 'data' => $upload]);
    }

    public function show($id)
    {
        $peta = UploadPeta::findOrFail($id);
        return response()->json($peta);
    }

    public function update(Request $request, $id)
    {
        $peta = UploadPeta::findOrFail($id);

        $request->validate([
            'nama_peta' => 'required|string|max:100',
            'tanggal_upload' => 'required|date',
            'uploader' => 'required|string|max:100',
            'format_file' => 'required|string|max:10',
            'file' => 'nullable|file|mimes:tif,tiff|max:512000', // Boleh kosong kalau gak upload ulang
        ]);

        // Jika ada file baru diupload
        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($peta->link_peta) {
                $filePath = str_replace(asset('/storage'), 'public', $peta->link_peta);
                Storage::delete($filePath);
            }

            // Simpan file baru
            $path = $request->file('file')->store('public/peta');
            $peta->link_peta = asset(Storage::url($path));
        }

        // Update data
        $peta->update([
            'nama_peta' => $request->nama_peta,
            'tanggal_upload' => $request->tanggal_upload,
            'uploader' => $request->uploader,
            'format_file' => $request->format_file,
        ]);

        return response()->json(['message' => 'Data peta berhasil diperbarui', 'data' => $peta]);
    }

    public function destroy($id)
    {
        $peta = UploadPeta::findOrFail($id);

        if ($peta->link_peta) {
            $filePath = str_replace(asset('/storage'), 'public', $peta->link_peta);
            Storage::delete($filePath);
        }

        $peta->delete();

        return response()->json(['message' => 'Data peta berhasil dihapus']);
    }

}
