<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// class
class SegiEmpat
{
    //properti
    public $panjang;
    public $lebar;

    // method
    public function luas()
    {
        return $this->panjang * $this->lebar;
    }
}
