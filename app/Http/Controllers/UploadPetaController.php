<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadPeta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class UploadPetaController extends Controller
{
    // ✅ GET ALL
    public function index()
    {
        $data = UploadPeta::orderBy('id_peta', 'asc')->get();
        return response()->json($data, 200);
    }

    // ✅ STORE (UPLOAD FILE)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_peta'   => 'required|string|max:100',
            'uploader'    => 'required|string|max:100',
            'file'        => 'required|file|mimes:tif,tiff|max:512000',
            'uploaded_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('peta', 'public');

                $upload = UploadPeta::create([
                    'nama_peta'   => $request->nama_peta,
                    'uploader'    => $request->uploader,
                    'format_file' => $file->getClientOriginalExtension(),
                    'link_peta'   => asset(Storage::url($path)),
                    'uploaded_at' => $request->uploaded_at 
                        ? Carbon::parse($request->uploaded_at) 
                        : now(),
                ]);

                return response()->json([
                    'message' => 'File berhasil diupload',
                    'data'    => $upload
                ], 201);
            }

            return response()->json(['message' => 'Tidak ada file diupload'], 400);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Upload gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ✅ SHOW DETAIL
    public function show($id)
    {
        $peta = UploadPeta::findOrFail($id);
        return response()->json($peta, 200);
    }

    // ✅ UPDATE
    public function update(Request $request, $id)
    {
        $peta = UploadPeta::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_peta'   => 'required|string|max:100',
            'uploader'    => 'required|string|max:100',
            'file'        => 'nullable|file|mimes:tif,tiff|max:512000',
            'uploaded_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('file')) {
                if ($peta->link_peta) {
                    $oldPath = str_replace(asset('storage') . '/', '', $peta->link_peta);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $file = $request->file('file');
                $path = $file->store('peta', 'public');
                $peta->link_peta   = asset(Storage::url($path));
                $peta->format_file = $file->getClientOriginalExtension();
            }

            $peta->nama_peta   = $request->nama_peta;
            $peta->uploader    = $request->uploader;
            $peta->uploaded_at = $request->uploaded_at 
                ? Carbon::parse($request->uploaded_at) 
                : $peta->uploaded_at;
            $peta->save();

            return response()->json([
                'message' => 'Data peta berhasil diperbarui',
                'data'    => $peta
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Update gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ✅ DELETE
    public function destroy($id)
    {
        $peta = UploadPeta::findOrFail($id);

        try {
            if ($peta->link_peta) {
                $oldPath = str_replace(asset('storage') . '/', '', $peta->link_peta);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $peta->delete();

            return response()->json(['message' => 'Data peta berhasil dihapus'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ✅ GET BY DATE
    public function getByDate(Request $request)
    {
        $tanggal = $request->query('tanggal');

        if (!$tanggal) {
            return response()->json(['message' => 'Parameter tanggal wajib diisi'], 400);
        }

        $data = UploadPeta::whereDate('uploaded_at', '=', $tanggal)
            ->orderBy('id_peta', 'asc')
            ->get();

        return response()->json($data, 200);
    }
}
