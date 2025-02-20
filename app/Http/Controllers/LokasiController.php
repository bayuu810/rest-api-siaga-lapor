<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LokasiController extends Controller
{

    public function index()
    {
        $lokasi = Lokasi::all();
        return response()->json(['data' => $lokasi, 'success' => true], 200);
    }
    public function addLokasi(Request $request)
    {
        // Periksa apakah pengguna memiliki peran admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'hanya admin yang bisa menambahkan lokasi'], 403);
        }

        $validated = Validator::make($request->all(), [
            'nama' => 'required|string|unique:lokasis|max:255',
        ]);

        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }

        try {

        $lokasi = Lokasi::create([
            'nama' => $request->nama,
        ]);

        return response()->json(['message' => 'lokasi berhasil ditambahkan.', 'success' => true , 'data' => $lokasi] , 201);
    }
    catch (\Exception $e) {
        return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data. '. $e->getMessage()], 500);
    }}
    public function deleteLokasiById($id)
    {
        try {
            // Cari kategori berdasarkan ID
            $lokasi = Lokasi::find($id);

            // Jika kategori tidak ditemukan
            if (!$lokasi) {
                return response()->json(['message' => 'Lokasi tidak ditemukan', 'success' => true], 404);
            }

            // Hapus user
            $lokasi->delete();

            return response()->json([
                'message' => 'Lokasi berhasil dihapus.',
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

}