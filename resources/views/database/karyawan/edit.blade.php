@extends('layouts.app')
@section('content')
    @include('database.karyawan.form', ['editing' => true])
@endsection
