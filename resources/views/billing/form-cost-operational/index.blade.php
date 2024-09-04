@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM COST OPERATIONAL</u></h1>
        </div>
    </div>
    {{-- if session has success, trigger sweet alert --}}
    @include('swal')
   
    <div class="row justify-content-left">


        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="80">
                <h4 class="mt-3">BACK</h3>
            </a>
        </div>
    </div>

</div>
@endsection
@push('js')
<script>

</script>
@endpush
