<div class="row justify-content-left">
    <h4 class="mt-3">TRANSAKSI</h4>
    <div class="col-md-2 text-center mt-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#uangJalan">
            <img src="{{asset('images/uang-jalan.svg')}}" alt="" width="70">
            <h4 class="mt-3">FORM KAS UANG JALAN</h4>
        </a>
        <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
        <div class="modal fade" id="uangJalan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="mb-3 mt-3">
                            <select class="form-select form-select-lg" name="" id="tipeKasUangJalan">
                                <option value="masuk">Permintaan Kas Uang Jalan</option>
                                <option value="keluar">Pengeluaran Uang Jalan</option>
                                <option value="pengembalian">Pengembalian Kas Uang Jalan</option>
                                <option value="penyesuaian">Penyesuaian Kas Uang Jalan</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="#" class="btn btn-primary" onclick="tipeFormKasUangJalan()">Lanjutkan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- BACK BUTTON --}}
    <div class="col-md-2 text-center mt-5">
        <a href="{{route('transaksi.nota-muat')}}" class="text-decoration-none">
            <img src="{{asset('images/muat.svg')}}" alt="" width="70">
            <h4 class="mt-3">NOTA MUAT
                {{-- <span class="text-danger">{{$data->where('status', 1)->count() > 0 ?
                    "(".$data->where('status', 1)->count().")" : '' }}</span> --}}
            </h4>
        </a>
    </div>
    <div class="col-md-2 text-center mt-5">
        <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
            <img src="{{asset('images/bongkar.svg')}}" alt="" width="70">
            <h4 class="mt-3">NOTA BONGKAR
                {{-- <span class="text-danger">{{$data->where('status', 2)->count() > 0 ?
                    "(".$data->where('status', 2)->count().")" : '' }}</span> --}}
            </h4>
        </a>
    </div>
    {{-- <div class="col-md-2 text-center mt-5">
        <a href="{{route('transaksi.sales-order')}}" class="text-decoration-none">
            <img src="{{asset('images/sales-order.svg')}}" alt="" width="70">
            <h4 class="mt-3">Sales Order <span class="text-danger">{{$data->whereIn('status', [1,2])->count() > 0
                    ? "(".$data->whereIn('status', [1,2])->count().")" : '' }}</span></h4>
        </a>
    </div> --}}
</div>
<hr>
<br>
<div class="row justify-content-left mt-5">
    <h2>STATISTIK PERFORM UNIT</h2>
    <div class="col-md-2 text-center mt-5">
        <a href="{{route('asisten-user.perform-unit')}}" class="text-decoration-none">
            <img src="{{asset('images/perform-unit.svg')}}" alt="" width="80">
            <h5 class="mt-3">BULANAN</h5>
        </a>
    </div>
    <div class="col-md-2 text-center mt-5">
        <a href="{{route('asisten-user.perform-unit-tahunan')}}" class="text-decoration-none">
            <img src="{{asset('images/perform-unit-tahunan.svg')}}" alt="" width="80">
            <h5 class="mt-3">TAHUNAN</h5>
        </a>
    </div>
    <div class="col-md-2 text-center mt-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
            <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="80">
            <h5 class="mt-3">UPAH GENDONG</h5>
        </a>
        <div class="modal fade" id="upahGendongId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="ugTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ugTitleId">
                            Pilih NOLAM UPAH GENDONG
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('asisten-user.upah-gendong')}}" method="get">
                        <div class="modal-body">
                            <div class="col-md-12 mb-3">
                                <select class="form-select" name="vehicle_id" id="vehicle_id">
                                    @foreach ($data as $d)
                                    <option value="{{$d->vehicle_id}}">{{$d->vehicle->nomor_lambung}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="col-md-2 text-center mt-5">
        <a href="{{route('statistik.perform-unit-tahunan')}}" class="text-decoration-none">
            <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="80">
            <h5 class="mt-3">MAINTENANCE</h5>
        </a>
    </div> --}}
    {{-- <div class="col-md-2 text-center mt-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ban_luar">
            <img src="{{asset('images/db-ban.svg')}}" alt="" width="80">
            <h5 class="mt-3">BAN LUAR</h5>
        </a>
        <div class="modal fade" id="ban_luar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="ban-luarTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ban-luarTitle">
                            Pilih NOLAM Ban Luar
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('statistik.ban-luar')}}" method="get">
                        <div class="modal-body">
                            <div class="col-md-12 mb-3">
                                <select class="form-select" name="vehicle_id" id="vehicle_ban">
                                    @foreach ($vehicle as $d)
                                    <option value="{{$d->id}}">{{$d->nomor_lambung}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>

    $('#vendorSelect').select2({
                placeholder: 'Pilih Vendor',
                width: '100%',
                dropdownParent: $('#vendorBayar')
            });
    function tipeFormKasBesar() {
            let val = document.getElementById('tipeKasBesar').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-besar.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-besar.keluar')}}";
            }
        }

        function tipeFormKasKecil() {
            let val = document.getElementById('tipeKasKecil').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-kecil.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-kecil.keluar')}}";
            } else if (val === 'void') {
                window.location.href = "{{route('kas-kecil.void')}}";
            }
        }

        function tipeFormCo() {
            let val = document.getElementById('formCo').value;
            if (val === 'masuk') {
                window.location.href = "{{route('billing.form-cost-operational.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-cost-operational.cost-operational')}}";
            }
        }

        function tipeFormKasUangJalan()
        {
            let val = document.getElementById('tipeKasUangJalan').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-uang-jalan.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-uang-jalan.keluar')}}";
            } else if (val === 'pengembalian') {
                window.location.href = "{{route('kas-uang-jalan.pengembalian')}}";
            }  else if (val === 'penyesuaian') {
                window.location.href = "{{route('kas-uang-jalan.penyesuaian')}}";
            }

        }

        function tipeFormLainlain()
        {
            let val = document.getElementById('formLainlain').value;
            if (val === 'masuk') {
                window.location.href = "{{route('form-lain-lain.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('form-lain-lain.keluar')}}";
            }
        }

        function tipeFormSetorPph()
        {
            let val = document.getElementById('formSetorPph').value;
            if (val === 'masuk') {
                window.location.href = "{{route('form-setor-pph.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('form-setor-pph.keluar')}}";
            }
        }

        function tipeFormBarang()
        {
            let val = document.getElementById('formBarangSelect').value;
            if (val === 'masuk') {
                window.location.href = "{{route('billing.form-barang.beli')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-barang.jual')}}";
            } else if(val === 'keluar-umum') {
                window.location.href = "{{route('billing.form-barang.jual-umum')}}";
            }
        }

        function tipeFormMaintenance()
        {
            let val = document.getElementById('fomrMaintenanceSelect').value;
            if (val === 'masuk') {
                window.location.href = "{{route('billing.form-maintenance.beli')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-maintenance.jual-vendor')}}";
            } else if (val === 'keluar-umum') {
                window.location.href = "{{route('billing.form-maintenance.jual-umum')}}";
            }
        }

        function tipeformVendor()
        {
            let val = document.getElementById('titipVendor').value;
            if (val === 'titipan') {
                window.location.href = "{{route('billing.vendor.titipan')}}";
            } else if(val === 'pelunasan') {
                window.location.href = "{{route('billing.vendor.pelunasan')}}";
            } else if(val === 'bayar') {
                window.location.href = "{{route('billing.vendor.bayar')}}";
            }
        }

        function tipeFormKasBon()
        {
            let val = document.getElementById('kasbonSelect').value;
            if (val === 'direksi') {
                window.location.href = "{{route('billing.kasbon.direksi.index')}}";
            } else if(val === 'staff') {
                window.location.href = "{{route('billing.kasbon.kas-bon-staff')}}";
            }
        }
</script>
@endpush

