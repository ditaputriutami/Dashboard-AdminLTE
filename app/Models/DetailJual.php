<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailJual extends Model
{
    protected $table = "detail_jual";
    public function barang()
    {
        return $this->belongsTo('\App\Models\Barang', 'barang_id');
    }
}
