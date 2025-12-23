<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = "pelanggan";
    protected $primaryKey = "id";
    static $rules = [
        'nama_pelanggan' => 'required',
        'jenis_kelamin' => 'required',
        'alamat' => 'required',
        'telp_hp' => 'required',
        'email' => 'required'
    ];
    protected $perPage = 20;
    /**
     * Attributes that should be mass-assignable.
     */
    protected $fillable = [
        'nama_pelanggan',
        'jenis_kelamin',
        'alamat',
        'telp_hp',
        'email'
    ];
}
