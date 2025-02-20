<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lokasi extends Model
{
    use HasFactory;

    protected $fillable = ['nama'];
    public function pengaduan()
    {
        return $this->hasMany(pengaduan::class);
    }
}
