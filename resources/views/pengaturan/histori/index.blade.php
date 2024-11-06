@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Histori Pesan WA</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <form action="{{route('pengaturan.histori.delete-sended')}}" method="post" id="hapusForm">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> HAPUS PESAN TERKIRIM</button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">Pesan</th>
                <th class="text-center align-middle">Tujuan</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->short_pesan()}}</td>
                <td class="text-center align-middle">{{$d->group_id}}</td>
                <td class="text-center align-middle">
                    @if ($d->status == 0)
                    <span class="badge bg-danger">Belum Terkirim</span>
                    @else
                    <span class="badge bg-success">Terkirim</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        @if ($d->status == 0)
                        <form action="{{route('pengaturan.histori.resend', $d->id)}}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-primary m-2"><i class="fa fa-paper-plane"></i> KIRIM ULANG</button>
                        </form>
                        @endif

                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    $('#data').DataTable();

    $('#hapusForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data yakin menghapus Pesan Terkirim?',
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
