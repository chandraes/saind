@extends('layouts.app')
@section('content')
    @include('billing.kasbon.form', ['isCicilan' => true])
@endsection
