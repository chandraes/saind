@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>MAINTENANCE <br> NOLAM {{$vehicle->nomor_lambung}}</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6 ">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td>
                        <a href="{{route('rekap.maintenance-vehicle.print', ['vehicle' => $vehicle->id])}}"
                            target="_blank"><img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> PRINT
                            PDF</a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-3 mt-2">
            {{-- <label for="tahun" class="form-label">Tahun</label> --}}
            <select class="form-select" name="tahun" id="tahun">
                @foreach ($dataTahun as $d)
                <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mt-2">
            <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
        </div>
    </div>
</div>

<div class="container-fluid table-responsive ml-3">
    <section id="form-input-odo" @if ($state == 1)
    hidden
    @endif>
        <form action="{{route('rekap.maintenance-vehicle.store-odometer')}}" method="post" id="masukForm">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">
            <div class="row">
                <div class="col-md-2">
                    <label for="odometer" class="form-label">Odometer</label>
                    <input type="text" class="form-control" name="odometer" id="odometer" aria-describedby="helpId" required
                        placeholder="Masukan Odometer" />
                </div>
                <div class="col-md-2">
                    <label for="filter_strainer" class="form-label">Filter Strainer</label>
                    <select class="form-select" name="filter_strainer" id="filter_strainer" required>
                        <option value="">-- Pilih Salah Satu -- </option>
                        <option value="1">Sudah</option>
                        <option value="0">Belum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_udara" class="form-label">Filter Strainer</label>
                    <select class="form-select" name="filter_udara" id="filter_udara" required>
                        <option value="">-- Pilih Salah Satu -- </option>
                        <option value="1">Sudah</option>
                        <option value="0">Belum</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="baut" class="form-label">Stock Baut (Buah)</label>
                    <input type="number" class="form-control" name="baut" id="baut" aria-describedby="helpId" required
                        placeholder="Masukan sisa Stock Baut" />
                </div>
                <div class="col-md-3">
                    <label for="equipment" class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary form-control">Simpan</button>
                </div>
        </form>
    </section>
</div>
<div class="row mt-3">
    <table class="table table-hover table-bordered" id="rekapTable" style="font-size:10px">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">Periode</th>
                <th class="text-center align-middle">Odometer</th>
                <th class="text-center align-middle">Filter Strainer</th>
                <th class="text-center align-middle">Filter Udara</th>
                <th class="text-center align-middle">Stock Baut (Buah)</th>
                @foreach ($equipment as $eq)
                <th class="text-center align-middle">{{ $eq->nama }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($weekly as $week => $equipmentCounts)
            <tr>
                <td class="text-center align-middle">{{ $week }}</td>
                <td class="text-center align-middle">{{ number_format($equipmentCounts['odometer'], 0, ',','.') }}</td>
                <td class="text-center align-middle">{{ $equipmentCounts['filter_strainer'] }}</td>
                <td class="text-center align-middle">{{ $equipmentCounts['filter_udara'] }}</td>
                <td class="text-center align-middle">{{ $equipmentCounts['baut'] }}</td>
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
            "info": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

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

        var nominal = new Cleave('#odometer', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

    });

</script>
@endpush
