@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PENGATURAN WA</u></h1>
        </div>
    </div>
    @if (session('error'))
    <script>
        Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{session('error')}}',
            })
    </script>
    @endif
    <form action="{{route('pengaturan.wa.update', $data->id)}}" method="post" id="masukForm">
        @csrf
        @method('PATCH')
        <div class="row mt-3">
            <div class="col-3 mb-3">
                <label for="untuk" class="form-label">Untuk</label>
                <input type="text" class="form-control" name="untuk" id="untuk" aria-describedby="helpId" placeholder=""
                    disabled value="{{$data->untuk}}">
            </div>
            <input type="hidden" name="group_id">
            <div class="col-3 mb-3">
                <label for="" class="form-label">Nama Group</label>
                <select class="form-select" name="nama_group" id="nama_group" required onchange="funGroup()">
                    @foreach ($group as $g)
                    <option value="{{$g['id']}}" {{$data->nama_group == $g['name'] ? 'selected' : ''}}>{{$g['name']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3 mb-3">
                <label for="" class="form-label">.</label>
                <button class="btn btn-success form-control" type="submit">Simpan</button>
            </div>
            <div class="col-3 mb-3">
                <label for="" class="form-label">.</label>
                <a class="btn btn-primary form-control" href="{{url()->previous()}}">Kembali</a>
            </div>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
        // group-api with select2
        $('#nama_group').select2({
            theme: 'bootstrap-5'
        });

        function funGroup(){
            // var group_id = $('#nama_group').val();
            // get name from select2
            var group_id = $('#nama_group option:selected').text();
            console.log(group_id);
            $('input[name="group_id"]').val(group_id);
        }

        // masukForm on submit, sweetalert confirm
        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
