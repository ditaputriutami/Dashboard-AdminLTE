@extends('app')
@section('content')
<h1>Hasil Perhitungan Balok</h1>
<table class="table table-striped table-bordered">
<tr><td>Panjang </td><td>{{$balok->panjang}}</td></tr>
<tr><td>Lebar </td><td>{{$balok->lebar}}</td></tr>
<tr><td>Tebal </td><td>{{$balok->lebar}}</td></tr>
<tr><td>Luas </td><td>{{$balok->volume()}}</td></tr>
</table>
@endsection