<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jual extends Model
{
    protected $table = "jual";
    protected $primaryKey = "id";
    public function pelanggan()
    {
        return $this->belongsTo('\App\Models\Pelanggan', 'pelanggan_id');
    }
}
