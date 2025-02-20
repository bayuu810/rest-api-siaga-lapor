<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;



class PengaduanController extends Controller
{

  
    public function index(Request $request)
  {

  // Mendapatkan role user yang sedang login
  $userRole = $request->user()->role;

  //Query pengaduan
  $pengaduanQuery = Pengaduan::with(['user', 'lokasi', 'kategory']);

  //jika user adalah petugas, filter pengaduan yang bukan bestatus 'proses'
  if ($userRole === 'petugas') {
    $pengaduanQuery->where('status', '!=', 'prosses');

  }
    //Eksekusi query dan ambil hasil
    $pengaduan = $pengaduanQuery->get();

    return response()->json(['data' => $pengaduan, 'success' => true], 200);
  }
    public function addpengaduan(Request $request)
    {
        //Validasi input
        $validate = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_pengaduan' => 'required|string',
            'foto' =>'required|image|mimes:jpg,png,jpeg|max:2048',
            'lokasi_id' => 'required|integer',
            'kategori_id' => 'required|integer',
        ]);

          //Jika Validasi Gagal, Kembalikan Pesan error
          if ($validate->fails()) {
             return response()->json([$validate->errors()], 403);
          }

          try {
            //proses upload gambar ke cloudinary
            $uploadedFile = Cloudinary::upload($request->file('foto')->getRealPath());

            //Mendapatkan  URL gambar yang diupload
            $fotoUrl = $uploadedFile->getSecurePath();

            //Simpan data ke pengaduan database
            $pengaduan = Pengaduan::create([
                'user_id' => auth()->user()->id,
                'judul' => $request->judul,  //menyimpan url dari claudinary
                'isi_pengaduan' => $request->isi_pengaduan,
                'foto' => $fotoUrl,
                'lokasi_id' => $request->lokasi_id,
                'kategori_id' => $request->kategori_id,
                'status' => 'proses'
            ]);
            return response()->json(['message' => 'Pengaduan berhasil ditambahkan', 'success' =>true], 201);
          } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage()], 500);
          }
    }

    public function getPengaduanByUserId()
    {
        try {
            // Mendapatkan user ID dari token/auth
            $userId = auth()->user()->id;

            // Mengambil data pengaduan berdasarkan user_id
            $pengaduan = Pengaduan::where('user_id', $userId)->get();

            // Jika tidak ada data pengaduan
            if ($pengaduan->isEmpty()) {
                return response()->json(['message' => 'Tidak ada pengaduan yang ditemukan', 'success' => false], 404);
            }

            return response()->json(['data' => $pengaduan, 'success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data. ' . $e->getMessage()], 500);
        }
    }

    public function getPengaduanById($id)
    {
    try {
        // Mencari pengaduan berdasarkan ID
        $pengaduan = Pengaduan::with(['user','lokasi', 'kategory'])->find($id);

        // Jika pengaduan tidak ditemukan
        if (!$pengaduan) {
            return response()->json(['message' => 'Pengaduan tidak ditemukan', 'success' => false], 404);
        }

        return response()->json(['data' => $pengaduan, 'success' => true], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Terjadi kesalahan saat mengambil data. ' . $e->getMessage()], 500);
    }
    }
  
//update status
    public function updateStatusPengaduan(request $request, $id)
    {
        Log::info($request->all());
        $validate = Validator::make($request->all(), [
            'status' => ['required', 'string', Rule::in(['proses', 'diterima', 'selesai'])],
        ]);
        

     if (!$validate->fails()) {
        Log::info('Validation Errors: ', $validate->errors()->all());
  }

  try {
     $pengaduan = Pengaduan::find($id);

     if (!$pengaduan) {
       return response()->json(['message' => 'Pengaduan tidak ditemukan', 'success' =>false], 404);
    }

      $pengaduan->status = $request->status;
      $pengaduan->save();

    
            return response()->json([
                'message' => 'Status pengaduan berhasil diperbarui dan email dikirim',
                'success' => true,
                'data' => $pengaduan
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memperbarui status. ' . $e->getMessage()], 500);
        }
    }
  

    public function deletePengaduanById($id)
    {
      try {
          // Cari kategori berdasarkan ID
          $pengaduan = Pengaduan::find($id);

          // Jika kategori tidak ditemukan
          if (!$pengaduan) {
              return response()->json(['message' => 'Pengaduan tidak ditemukan', 'success' => true], 404);
          }

          // Hapus user
          $pengaduan->delete();

          return response()->json([
              'message' => 'pengaduan berhasil dihapus.',
              'success' => true,
          ], 200);
      } catch (\Exception $e) {
          return response()->json([
              'error' => 'Terjadi kesalahan saat menghapus data. ' . $e->getMessage(),
              'success' => false
          ], 500);
      }
  }
}