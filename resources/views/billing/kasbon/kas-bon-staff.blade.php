@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Metode Kasbon Staff</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="justify-content-center px-5">
        <div class="px-5">
            <label for="metodeSelect" class="form-label">Metode</label>
            <div class="input-group mb-3">
                <select class="form-select" name="metodeSelect" id="metodeSelect" required>
                    <option value="potong">Potong Gaji</option>
                    <option value="cicil">Cicilan</option>
                </select>
                <button class="btn btn-outline-primary" type="button" onclick="metodeKas()">Lanjutkan</button>
                <a href="{{route('billing.index')}}" class="btn btn-outline-danger">Keluar</a>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
    <script>
        function metodeKas()
        {
            let val = document.getElementById('metodeSelect').value;
            if (val === 'potong') {
                window.location.href = "{{route('billing.kasbon.index')}}";
            } else if(val === 'cicil') {
                window.location.href = "{{route('billing.kasbon.kas-bon-cicil')}}";
            }
        }
    </script>
@endpush
