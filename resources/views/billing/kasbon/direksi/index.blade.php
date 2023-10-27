@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KASBON DIREKSI</u></h1>
        </div>
    </div>
    <div class="row mt-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <select class="form-select" name="kas" id="kasBonSelect">
                            <option value="kasbon">Kasbon</option>
                            <option value="bayar">Bayar Kasbon</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <button class="btn btn-primary" onclick="kasbonDireksi()">Lanjutkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    function kasbonDireksi() {
            let val = document.getElementById('kasBonSelect').value;
            if (val === 'kasbon') {
                window.location.href = "{{route('billing.kasbon.direksi.kasbon')}}";
            } else if (val === 'bayar') {
                window.location.href = "{{route('billing.kasbon.direksi.bayar')}}";
            }
        }
</script>
@endpush
