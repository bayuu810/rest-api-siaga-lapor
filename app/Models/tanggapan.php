<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tanggapan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'pengaduan_id', 'tanggapan', 'foto'];

    public function pengaduan()
    {
        return $this->belongsTo(pengaduan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

