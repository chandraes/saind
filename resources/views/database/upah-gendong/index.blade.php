@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 upah-workspace">
    @include('swal')
    <header class="upah-heading">
        <div>
            <nav class="d-flex flex-wrap gap-3 mb-3" aria-label="Navigasi Upah Gendong">
                <a href="{{ route('home') }}"><i class="fa fa-home me-1" aria-hidden="true"></i> Dashboard</a>
                @if (in_array(auth()->user()->role, ['admin', 'su'], true))
                    <a href="{{ route('database') }}">Database</a>
                @else
                    <a href="{{ route($vendorId !== null ? 'statistik.upah-gendong' : 'statisik.index') }}">Hasil Upah Gendong</a>
                @endif
            </nav>
            <h1>Pengaturan Upah Gendong</h1>
            <p class="mb-0 text-muted">Kelola tarif, batas tonase, dan rekening pembayaran per kendaraan.</p>
        </div>
        @include('database.upah-gendong.create')
    </header>
@if ($errors->any())
<div class="container">
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
@include('database.upah-gendong.edit')
<section class="upah-panel" aria-labelledby="upah-list-title">
    <div class="upah-panel-heading">
        <h2 id="upah-list-title" class="h5 fw-bold mb-1">Kendaraan terdaftar</h2>
        <span class="text-muted">{{ $data->count() }} pengaturan aktif</span>
    </div>
    <div class="p-3">
    <table class="table table-hover table-bordered align-middle w-100" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th scope="col">No.</th>
                <th scope="col">No. Lambung</th>
                <th scope="col" class="text-end">Tarif</th>
                <th scope="col" class="text-end">Min. Tonase</th>
                <th scope="col">Driver</th>
                <th scope="col">Pengurus</th>
                <th scope="col">Rekening</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-muted">{{ $loop->iteration }}</td>
                <td class="fw-semibold text-nowrap">{{ $d->vehicle?->nomor_lambung ?? '–' }}</td>
                <td class="text-end text-nowrap fw-semibold">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                <td class="text-end text-nowrap">{{ $d->tonase_min }} ton</td>
                <td>{{ $d->nama_driver }}<small class="d-block text-muted">Masuk {{ $d->id_tanggal_masuk_driver }}</small></td>
                <td>{{ $d->nama_pengurus }}<small class="d-block text-muted">Masuk {{ $d->id_tanggal_masuk_pengurus }}</small></td>
                <td>{{ $d->bank }} <span class="text-nowrap">{{ $d->no_rek }}</span><small class="d-block text-muted">{{ $d->nama_rek }}</small></td>
                <td class="text-center align-middle">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" data-bs-toggle="modal"
                            data-bs-target="#editUg" onclick="editUg(@js($d), @js(route('database.upah-gendong.update', $d)))"><i
                                class="fa fa-edit me-1" aria-hidden="true"></i> Ubah</button>
                        <form action="{{route('database.upah-gendong.destroy', ['ug' => $d])}}" method="post"
                            data-upah-delete="{{ $d->vehicle?->nomor_lambung }}" @if ($vendorId !== null) data-upah-password @endif>
                            @csrf
                            @method('delete')
                            @if ($vendorId !== null)<input type="hidden" name="password">@endif
                            <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap"><i class="fa fa-trash me-1" aria-hidden="true"></i> Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</section>
</div>
@endsection
@include('database.upah-gendong.styles')
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script>
      function editUg(data, url) {
            $('#edit_vehicle_id').val(data.vehicle_id).trigger('change');
            document.getElementById('edit_nama_driver').value = data.nama_driver;
            $('#edit_nominal').data('cleave').setRawValue(data.nominal);
            document.getElementById('edit_tonase_min').value = data.tonase_min;
            document.getElementById('edit_nama_pengurus').value = data.nama_pengurus;
            document.getElementById('edit_tanggal_masuk_pengurus').value = data.id_tanggal_masuk_pengurus;
            document.getElementById('edit_tanggal_masuk_driver').value = data.id_tanggal_masuk_driver;
            document.getElementById('edit_nama_rek').value = data.nama_rek;
            document.getElementById('edit_no_rek').value = data.no_rek;
            document.getElementById('edit_bank').value = data.bank;
            document.getElementById('editForm').action = url;
        }

    $(document).ready(function() {

        flatpickr("#edit_tanggal_masuk_driver", {
            dateFormat: "d-m-Y",
        });

        flatpickr("#edit_tanggal_masuk_pengurus", {
            dateFormat: "d-m-Y",
        });

        flatpickr(".tanggal_flatpick", {
            dateFormat: "d-m-Y",
        });

        $('#karyawan-data').DataTable({
            paging: false,
            scrollX: true,
            scrollY: '52vh',
            scrollCollapse: true,
            order: [],
            columnDefs: [{targets: -1, orderable: false}],
            language: {search: 'Cari', emptyTable: 'Belum ada pengaturan Upah Gendong.', zeroRecords: 'Kendaraan yang cocok tidak ditemukan.', info: '_TOTAL_ pengaturan', infoEmpty: '0 pengaturan', infoFiltered: ''}
        });

        $('[data-upah-money]').each(function () {
            $(this).data('cleave', new Cleave(this, {
                numeral: true,
                numeralDecimalScale: 0,
                numeralPositiveOnly: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            }));
        });

        $('#vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#tambahSponsorId')
        });


        $('#edit_vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#editUg')
        });

        $('#masukForm, #editForm, [data-upah-delete]').on('submit', async function (event) {
            event.preventDefault();
            if (this.dataset.confirming) return;
            this.dataset.confirming = 'true';

            const deleting = this.hasAttribute('data-upah-delete');
            const needsPassword = this.hasAttribute('data-upah-password');
            const nolam = deleting ? this.dataset.upahDelete : $(this).find('select option:selected').text();
            const action = deleting ? 'Hapus' : this.id === 'editForm' ? 'Ubah' : 'Aktifkan';
            let submitted = false;
            try {
                const result = await Swal.fire({
                    title: action + ' Upah Gendong?',
                    text: 'Kendaraan: ' + nolam,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: action,
                    cancelButtonText: 'Batal',
                    ...(needsPassword ? {
                        input: 'password',
                        inputLabel: 'Password akun',
                        inputValidator: value => value ? undefined : 'Masukkan password akun Anda.'
                    } : {})
                });
                if (!result.isConfirmed) return;

                if (needsPassword) this.elements.password.value = result.value;
                const money = this.querySelector('[data-upah-money]');
                if (money) this.querySelector('[data-upah-canonical]').value = $(money).data('cleave').getRawValue();

                Swal.fire({title: 'Memproses pengaturan Upah Gendong', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
                HTMLFormElement.prototype.submit.call(this);
                submitted = true;
            } finally {
                if (!submitted) delete this.dataset.confirming;
            }
        });
    } );

</script>
@endpush
