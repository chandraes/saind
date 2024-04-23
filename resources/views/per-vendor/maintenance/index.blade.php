@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>MAINTENANCE <br> NOLAM {{$vehicle->nomor_lambung}}</u></h1>
            <h1>{{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6 ">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <form action="{{route('per-vendor.maintenance-vehicle.print')}}" method="get" target="_blank">
                            <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                        <button type="submit" class="btn"><img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> PRINT
                            PDF</button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-md-3 mt-2">
            {{-- <label for="tahun" class="form-label">Tahun</label> --}}
            <form action="{{route('per-vendor.maintenance-vehicle')}}" method="get">
                <select class="form-select" name="tahun" id="tahun">
                    @foreach ($dataTahun as $d)
                    <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                    @endforeach
                </select>
                <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">
        </div>
        <div class="col-md-3 mt-2">
            <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
        </div>
        </form>
    </div>
</div>
<div class="container-fluid">
    <div class="row mt-2">
        <div class="col-md-6 d-flex justify-content-start">
            <table>

                <tr>
                    <td>
                        <h5>Nama Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->driver}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Tgl Masuk Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{ \Carbon\Carbon::parse($vehicle->tanggal_masuk_driver)->format('d-m-Y') }}</h5>
                    </td>
                </tr>

            </table>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tr>
                    <td>
                        <h5>Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->pengurus}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Tgl Masuk Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{ \Carbon\Carbon::parse($vehicle->tanggal_masuk_pengurus)->format('d-m-Y') }}</h5>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid table-responsive ml-3">
    <section id="form-input-odo">
        <form action="{{route('per-vendor.maintenance-vehicle.store-odo')}}" method="post" id="masukForm">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">
            <div class="row">
                <div class="col-md-2">
                    <label for="odometer" class="form-label">Odometer</label>
                    <input type="text" class="form-control" name="odometer" id="odometer" aria-describedby="helpId" value="{{$odo}}"
                        required placeholder="Masukan Odometer" />
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
                    <label for="filter_udara" class="form-label">Filter Udara</label>
                    <select class="form-select" name="filter_udara" id="filter_udara" required>
                        <option value="">-- Pilih Salah Satu -- </option>
                        <option value="1">Sudah</option>
                        <option value="0">Belum</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="baut" class="form-label">Stock Baut</label>
                    <input type="number" class="form-control" name="baut" id="baut" aria-describedby="helpId" required value="{{$baut}}"
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
    <table class="table table-hover table-bordered" id="rekapTable" style="font-size:12px">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">Periode</th>
                <th class="text-center align-middle">Odo<br>Meter</th>
                <th class="text-center align-middle">Filter<br>Strainer</th>
                <th class="text-center align-middle">Filter<br>Udara</th>
                <th class="text-center align-middle">Stock<br>Baut</th>
                @foreach ($equipment as $eq)
                <th class="text-center align-middle">{!! implode('<br>', explode(' ', $eq->nama)) !!}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($weekly as $week => $equipmentCounts)
            <tr>
                <td class="text-center align-middle">{{ $week }}</td>
                <td class="text-center align-middle">
                    @if ($equipmentCounts['odometer'] == 0)
                    -
                    @else
                    {{ number_format($equipmentCounts['odometer'], 0, ',','.') }}
                    @endif
                  </td>
                <td class="text-center align-middle">
                    @if ($equipmentCounts['filter_strainer']  == 0)
                    -
                    @elseif ($equipmentCounts['filter_strainer']  == 1)
                    <i class="fa fa-check" style="font-size: 15px""></i>
                    @else
                    -
                    @endif
                </td>
                <td class="text-center align-middle">
                    @if ($equipmentCounts['filter_udara']  == 0)
                    -
                    @elseif ($equipmentCounts['filter_udara']  == 1)
                    <i class="fa fa-check" style="font-size: 15px""></i>
                    @else
                    -
                    @endif
                </td>
                <td class="text-center align-middle">{{ $equipmentCounts['baut'] }}</td>
                @foreach ($equipment as $eq)
                <td class="text-center align-middle">
                    @if ($equipmentCounts[$eq->nama] == 0)
                    -
                    @else
                    {{ $equipmentCounts[$eq->nama] }}
                    @endif
                </td>
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
