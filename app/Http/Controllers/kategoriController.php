<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kategori;

class kategoriController extends Controller
{
    public function index()
    {
        $kategori = kategori::all();
        return response()->json(['data' =>$kategori, 'success' => true], 200);
    }
    public function addKategori(Request $request)
    {
        //periksa apakah pengguna memiliki peran admin
        if (Auth::user()->role!=='admin') {
            return response()->json(['error' => 'hanya admin yang bisa menambahkan kategori'], 403);
        }
        try {
            $kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
            ]);
            return response()->json(['message' => 'Kategori Berhasil ditambahkan.', 'success' => true ,'kategori' => $kategori], 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data', 'success'=> false], 404);
        }
    }
    public function deleteKategoriById($id)
    {
        try{
            // Cari kategori Berdasarkan id
            $kategori = kategori::find($id);

            //jika kategori tidak ditemukan 
            if (!$kategori) {
                return response()->json(['message' => 'Kategori tidak ditemukan', 'success' =>false], 404);
            }

            //hapus user
            $kategori->delete();

            return response()->json([
                'message' => 'Kategori berhasil dihapus.',
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