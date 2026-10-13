@extends('layouts.app')
@section('content')
    @include('database.karyawan.form', ['editing' => false])
@endsection
