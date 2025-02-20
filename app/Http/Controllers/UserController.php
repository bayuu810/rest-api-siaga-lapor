<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json(['success' => true, 'data' => $users], 200);
    }
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(),[
            "name" => "required|string|max:20",
            "nik" => "required|string|max:16|unique:users",
            "email" => "required|string|max:25|email|unique:users",
            "no_telpon"=> "required|string|regex:/^\+?[0-9]{8,15}$/",
            "password"=> "required|string|min:8",
            "role" => "nullable|string",
            "profile_picture" => "nullable|image|mimes:jpg,png,jpeg|max:2048"
        ]);
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }

        $role = $request->role ?? 'masyarakat';

        try {
                $profile_picture_url = null;

             if ($request->hasFile('profile_picture')) {

                 $uploadedFile = Cloudinary::upload($request->file('profile_picture')->getRealPath());
        
                 $profile_picture_url = $uploadedFile->getSecurePath();
          }

          $user = User::create([
            'name' => $request->name,
            'nik' => $request->nik,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telpon' => $request->no_telpon,
            'role' => $role,
            'profile_picture' => $profile_picture_url,

          ]);
          
          return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'user' => $user,
            'success' => true,
          ],201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat menyimpan data. ' .$e->getMessage()
            ], 500);
        }

    }
    public function login(Request $request)
    {

        $validated = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Email tidak ditemukan'], 403);
            }
             
            if (!Hash::check($request->password, $user->password)){
                return response()->json(['message' => 'Password salah'], 403);   
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
                'success' => true,
                'message' => 'Selamat Anda berhasil login!',
            ], 200);

        } catch (\Exception $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function updateUserById(Request $request, $id)
    {
        // Validasi input
        $validated = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => "nullable|string|email|max:255|unique:users,email,{$id}",
            'current_password' => 'required_with:password|string|min:8', // Password lama wajib jika ingin mengganti password
            'password' => 'nullable|string|min:8|confirmed', // Konfirmasi password baru
            'no_telpon' => 'nullable|string|max:15|regex:/^\\+?[0-9]{8,15}$/',
            'role' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpg,png,jpg|max:2048'
        ]);
    
        // Jika validasi gagal, kembalikan pesan error
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 400);
        }
    
        try {
            // Cari user berdasarkan ID
            $user = User::find($id);
    
            // Jika user tidak ditemukan
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan', 'success' => false], 404);
            }
    
            // Validasi password lama sebelum mengganti password baru
            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json(['error' => 'Password lama tidak cocok'], 400);
                }
                $user->password = Hash::make($request->password);
            }
    
            // Update data user jika ada perubahan
            $user->name = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email;
            $user->no_telpon = $request->no_telpon ?? $user->no_telpon;
            $user->role = $request->role ?? $user->role;
    
            // Proses upload gambar jika ada
            if ($request->hasFile('profile_picture')) {
                // Upload gambar baru ke Cloudinary
                $uploadedFile = Cloudinary::upload($request->file('profile_picture')->getRealPath());
                $user->profile_picture = $uploadedFile->getSecurePath();
            }
    
            // Simpan perubahan
            $user->save();
    
            return response()->json([
                'message' => 'User berhasil diperbarui.',
                'user' => $user,
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui data. ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' =>'Berhasil keluar', 'success' => true], 200);
    }
}