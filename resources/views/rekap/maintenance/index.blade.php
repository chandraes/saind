@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>MAINTENANCE NOLAM {{$vehicle->nomor_lambung}}</u></h1>
        </div>
    </div>
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

            <strong>{{session('success')}}</strong>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="row">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>{{session('error')}}</strong>
        </div>
    </div>
    @endif
    <div class="row justify-content-between mt-3">
        <div class="col-md-6 ">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-3 mt-2">
            {{-- <label for="tahun" class="form-label">Tahun</label> --}}
            <select class="form-select" name="tahun" id="tahun">
                {{-- @foreach ($dataTahun as $d)
                <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                @endforeach --}}
            </select>
        </div>
        <div class="col-md-3 mt-2">
            <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
        </div>
    </div>
</div>

<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Periode</th>
                    <th class="text-center align-middle">Odometer</th>
                    @foreach ($equipment as $eq)
                    <th class="text-center align-middle">{{ $eq->nama }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($weekly as $week => $equipmentCounts)
                <tr>
                    <td class="text-center align-middle">{{ $week }}</td>
                    <td class="text-center align-middle">0</td>
                    @foreach ($equipment as $eq)
                    <td class="text-center align-middle">{{ $equipmentCounts[$eq->nama] ?? 0 }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "fixedColumns": {
                "leftColumns": 4,
                "rightColumns": 2
            },

        });

    });

</script>
@endpush
