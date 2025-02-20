<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tanggapan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class TanggapanController extends Controller
{

    public function index()
    {
        $tanggapan = tanggapan::all();
        return response()->json(['message' => 'Data Berhasil Diambil','success'=>true,'data'=>$tanggapan]);
    }
    public function addTanggapan(Request $request,  $pengaduan_id)
    {
        // Periksa apakah petugas memiliki peran petugas
        if (Auth::user()->role !== 'petugas' ) {
            return response()->json(['error' => 'hanya petugas yang bisa memberikan tanggapan'], 403);
        }

        $validated = Validator::make($request->all(),[
            'tanggapan' => 'required|string',
            'foto'=> 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validated->fails()) {
            return response()->json([$validated->errors()], 403);
        }

        //periksa apakah tanggapan untuk pengaduan ini sudah ada 
        $existingTanggapan = tanggapan::where('pengaduan_id', $pengaduan_id)->first();
        if ($existingTanggapan) {
            return response()->json(['message' => 'Tanggapan untuk pengaduan ini sudah ada'], 409);
        }

        try {
            $image = $request->file('foto');
            $uploadResult = Cloudinary::upload($image->getRealPath(),[
                'folder' => 'tanggapan_foto',
            ]);

            //Mendapatkan URL gambar setelah diupload
            $fotoUrl = $uploadResult->getSecurePath();

            //Menyimpan data tanggapan ke database
            $tanggapan = tanggapan::create([
                'pengaduan_id' =>$pengaduan_id,
                'user_id' => auth()->user()->id,
                'tanggapan' => $request->tanggapan,
                'foto' => $fotoUrl, //Menyimpan gambar di Claudinary
            ]);

            return response()->json(['message' =>'Tanggapan Berhasil di kirim', 'data'=> $tanggapan, 'success' =>true], 201);
        } catch (\Exception $e) {
            return response()->json(['error' =>'Terjadi kesalahan saat menyimpan data.' .$e->getMessage()], 500);
        }
    }

    public function getTanggapanByPengaduanId($pengaduan_id)
    {
        try {
            //Ambil tanggapan  berdasarkan pengaduan_id
            $tanggapan = Tanggapan::where('pengaduan_id', $pengaduan_id)->with('user')->get();

            if ($tanggapan-> isEmpty()) {
                return response()->json(['message' => 'Tidak ada tanggapan untuk pengaduan ini', 'success' => false], 404);
            }

            return response()->json(['data' => $tanggapan, 'success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data. ' .$e->getMessage()], 500);
        }
    }
}