<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KataBijakController extends Controller
{
    public function kata()
    {
        echo "Rajin Pangkal Pandai";
    }
    public function pepatah()
    {
        $kataku ="Sedikit demi sedikit lama-lama menjadi bukit";
        $kampus ="UTDI Keren";
        return view('kata-bijak.pepatah',compact('kataku', 'kampus'));
    }
}
