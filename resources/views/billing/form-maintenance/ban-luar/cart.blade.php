@extends('layouts.app')

@section('content')
<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Konfirmasi Invoice Penggantian Ban</h3>
            <p class="text-muted mb-0">Tinjau kembali rincian ban sebelum menerbitkan invoice transaksi.</p>
        </div>
        <div>
            <a href="{{ route('billing.form-maintenance.ban-luar') }}" class="btn btn-outline-primary fw-bold">
                <i class="fa fa-plus me-1"></i> Tambah Ban Lagi
            </a>
        </div>
    </div>

    @include('billing.form-maintenance.filter-oli.feedback')

    <div class="row">
        <!-- RINGKASAN UNIT KENDARAAN -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="row text-center text-md-start align-items-center">
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Nomor Lambung</span>
                            <span class="fw-bold fs-4">{{ $vehicle->nomor_lambung }}</span>
                        </div>
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Vendor</span>
                            <span class="fw-bold fs-6">{{ $vehicle->nama_vendor ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Pengurus</span>
                            <span class="fw-bold fs-6">{{ $vehicle->pengurus ?? '-' }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-white-50 fs-7 d-block">Driver</span>
                            <span class="fw-bold fs-6">{{ $vehicle->nama_driver ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RINCIAN KERANJANG & CHECKOUT FORM -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-shopping-cart text-warning me-2"></i> Rincian Item Ban Dalam Keranjang
                    </h5>
                    <button type="button" id="btn-clear-cart" class="btn btn-sm btn-outline-danger">
                        <i class="fa fa-trash me-1"></i> Kosongkan Keranjang
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>NO</th>
                                    <th>POSISI BAN</th>
                                    <th>MEREK BAN</th>
                                    <th>NO. SERI BAN</th>
                                    <th>KONDISI AWAL</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold text-primary">{{ $item->posisiBan->nama }}</td>
                                        <td class="fw-bold text-uppercase">{{ $item->merk }}</td>
                                        <td class="text-uppercase">{{ $item->no_seri }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark fs-7">{{ $item->kondisi }}%</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-item" data-id="{{ $item->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FORM CHECKOUT METODE PEMBAYARAN -->
                <div class="card-footer bg-light p-4">
                    <form action="{{ route('billing.form-maintenance.ban-luar.checkout') }}" method="POST" data-maintenance-form data-confirm="Kirim penggantian ban untuk otorisasi? Saldo UJ Ditahan Ban dipotong saat disetujui admin/SU." novalidate>
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                        <h5 class="fw-bold mb-3">Pembayaran penggantian ban</h5>
                        <div class="row g-3">
                            <div class="col-md-6"><label for="ban-payment" class="form-label fw-semibold">Metode pembayaran</label>
                                <select id="ban-payment" name="pembayaran" class="form-select maintenance-select" data-payment-method required>
                                    @php($paymentOptions = \App\Services\MaintenancePaymentService::optionsFor(auth()->user()?->role, 'uj_ditahan_ban'))
                                    @foreach ($paymentOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('pembayaran', array_key_first($paymentOptions)) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-none" data-uj-balance hidden><div class="border border-success rounded p-3 h-100 bg-white"><span class="small text-muted d-block">Seluruh saldo UJ Ditahan Ban kendaraan</span><strong class="text-success fs-4">Rp {{ number_format($saldoUjDitahanBan, 0, ',', '.') }}</strong><div class="small text-muted">Saldo positif seluruh bulan. Dipotong dari bulan tertua saat disetujui.</div></div></div>
                        </div>
                        <div class="row g-3 mt-2" data-bank-fields>
                            <div class="col-md-6"><label for="ban-amount" class="form-label">Total nominal (Rp)</label><input id="ban-amount" class="form-control" data-maintenance-money inputmode="numeric" value="{{ old('total_nominal') }}" required autocomplete="off"><input type="hidden" name="total_nominal" data-maintenance-money-value value="{{ old('total_nominal') }}"></div>
                            <div class="col-md-6"><label for="ban-bank" class="form-label">Bank tujuan</label><input id="ban-bank" class="form-control" name="nama_bank" maxlength="50" value="{{ old('nama_bank') }}" required></div>
                            <div class="col-md-6"><label for="ban-account" class="form-label">Nomor rekening</label><input id="ban-account" class="form-control" name="nomor_rekening" maxlength="50" inputmode="numeric" value="{{ old('nomor_rekening') }}" required></div>
                            <div class="col-md-6"><label for="ban-account-name" class="form-label">Atas nama</label><input id="ban-account-name" class="form-control" name="nama_rekening" maxlength="100" value="{{ old('nama_rekening') }}" required></div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between gap-3 border-top pt-4 mt-4"><a href="{{ route('billing.form-maintenance.ban-luar') }}" class="btn btn-outline-secondary">Kembali tambah item</a><button type="submit" class="btn btn-success px-4">Kirim untuk otorisasi</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function(){
        // Hapus item keranjang
        $('.btn-delete-item').click(function() {
            var id = $(this).data('id');
            var deleteRoute = "{{ route('billing.form-maintenance.ban-luar.cart.delete', ':id') }}";

            Swal.fire({
                title: 'Hapus Item Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteRoute.replace(':id', id),
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        error: function() { Swal.fire({icon: 'error', title: 'Gagal menghapus item', text: 'Periksa koneksi dan coba kembali.'}); },
                        success: async function() {
                            await Swal.fire({icon: 'success', title: 'Item berhasil dihapus'});
                            location.reload();
                        }
                    });
                }
            });
        });

        // Kosongkan keranjang
        $('#btn-clear-cart').click(function() {
            var vehicleId = "{{ $vehicle->id }}";
            var clearRoute = "{{ route('billing.form-maintenance.ban-luar.cart.clear', ':id') }}";

            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: 'Semua item keranjang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus Semua'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: clearRoute.replace(':id', vehicleId),
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        error: function() { Swal.fire({icon: 'error', title: 'Keranjang gagal dikosongkan', text: 'Periksa koneksi dan coba kembali.'}); },
                        success: async function() {
                            await Swal.fire({icon: 'success', title: 'Keranjang berhasil dikosongkan'});
                            window.location.href = "{{ route('billing.form-maintenance.ban-luar') }}";
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
