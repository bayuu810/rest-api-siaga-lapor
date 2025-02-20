<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pengaduan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'judul',
        'isi_pengaduan',
        'foto',
        'lokasi_id',
        'kategori_id',
        'status'
    ];

    protected $table = 'pengaduan';


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
     
    public function tanggapan()
    {
        return $this->hasMany(tanggapan::class);
    }

    public function kategory()
    {
        return $this->belongsTo(kategori::class, 'kategori_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(lokasi::class, 'lokasi_id');
    }
}
