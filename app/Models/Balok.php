<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balok extends SegiEmpat
{
    public $tebal;
    public function volume()
    {
        return $this->tebal * $this->panjang * $this->lebar;
    }
}
