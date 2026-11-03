@extends('layouts.app')
@section('content')
@include('billing.kas-besar.deposit-form', ['isWithdrawal' => true])
@endsection
