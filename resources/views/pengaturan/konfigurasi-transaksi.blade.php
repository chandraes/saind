@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h1 class="fw-bold"><u>BATASAN</u></h1>
        </div>
    </div>

    @include('swal')

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="col-md-4">
            <table class="table table-borderless">
                <tr class="text-center">
                    <td>
                        <a href="{{route('home')}}" class="text-decoration-none text-dark">
                            <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30">
                            <div class="small fw-bold">Dashboard</div>
                        </a>
                    </td>
                    <td>
                        <a href="{{route('pengaturan')}}" class="text-decoration-none text-dark">
                            <img src="{{asset('images/pengaturan.svg')}}" alt="pengaturan" width="30">
                            <div class="small fw-bold">Pengaturan</div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- SECTION: BATASAN UMUM (CARD GRID) --}}
    <div class="row mt-4 mb-5">
        <div class="col-12 mb-3">
            <h2 class="fw-bold text-secondary"><i class="fa fa-cogs me-2"></i>Batasan Umum</h2>
            <hr>
        </div>

        <div class="row">
            @foreach ($batasanUmum as $b)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 rounded-4 transition-hover">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <p class="text-muted fw-bold mb-1" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                                {{ $b->untuk }}
                            </p>
                            <h3 class="text-primary fw-bolder mb-0 mt-2">
                                @if(str_contains(strtolower($b->untuk), 'tonase'))
                                    {{ $b->nf_nilai }} <span class="fs-6 text-secondary fw-normal">Ton</span>
                                @else
                                    <span class="fs-6 text-secondary fw-normal">Rp</span> {{ $b->nf_nilai }}
                                @endif
                            </h3>
                        </div>
                        <div class="mt-4 text-end">
                            <button class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold"
                                data-bs-toggle="modal"
                                data-bs-target="#editBatasanModal"
                                onclick="editBatasan({{ $b }}, {{ $b->id }})">
                                <i class="fa fa-edit me-1"></i> Ubah Nilai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION: BATASAN KHUSUS (TABLE) --}}
    <div class="row mt-3">
        <div class="col-12 mb-3">
            <h2 class="fw-bold text-secondary"><i class="fa fa-shield-alt me-2"></i>Batasan Khusus</h2>
            <hr>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center align-middle py-3">No</th>
                                    <th class="text-center align-middle py-3">Nama</th>
                                    <th class="text-center align-middle py-3">Otomatis Tutup Dalam</th>
                                    <th class="text-center align-middle py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $d)
                                <tr>
                                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                                    <td class="text-center align-middle fw-bold text-dark">{{$d->nama}}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-light border btn-sm px-3 rounded-pill" data-bs-toggle="modal"
                                            data-bs-target="#waktuModal{{$d->id}}">
                                            <i class="fa fa-clock me-1 text-primary"></i> {{$d->waktu_aktif}} Jam
                                        </button>

                                        <div class="modal fade" id="waktuModal{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                                            data-bs-keyboard="false" role="dialog" aria-labelledby="title{{$d->id}}">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold" id="title{{$d->id}}">Waktu Aktif</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{route('pengaturan.konfigurasi-transaksi.update-jam', ['konfigurasi' => $d->id])}}" method="post">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body">
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" name="waktu_aktif" value="{{$d->waktu_aktif}}" required>
                                                                <span class="input-group-text">Jam</span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <form action="{{route('pengaturan.konfigurasi-transaksi.update', $d->id)}}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-{{$d->status ? 'success' : 'danger'}} btn-sm rounded-pill px-4 shadow-sm">
                                                {{$d->status ? 'Tutup' : 'Buka'}}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT BATASAN UMUM --}}
<div class="modal fade" id="editBatasanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Ubah Batasan Umum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBatasanForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Penggunaan</label>
                        <input type="text" class="form-control bg-light border-0" id="untukDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nilaiInput" class="form-label text-muted fw-bold">Nilai Baru</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 text-muted" id="prefixSymbol">Rp</span>
                            <input type="text" class="form-control border-start-0 border-end-0" name="nilai" id="nilaiInput" required>
                            <span class="input-group-text bg-white border-start-0 text-muted" id="suffixSymbol" style="display: none;">Ton</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .transition-hover { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
    .transition-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>
@endpush

@push('js')
<script>
    // Variabel global untuk menyimpan instance Cleave
    let cleaveInstance = null;

    function editBatasan(data, id) {
        let isTonase = data.untuk.toLowerCase().includes('tonase');

        // Atur Action Route
        $('#editBatasanForm').attr('action', '/pengaturan/batasan/update/' + id);

        // Isi data ke modal
        $('#untukDisplay').val(data.untuk);

        // Set nilai awal sebelum dimasking
        $('#nilaiInput').val(data.nf_nilai);

        if (isTonase) {
            // Setup UI untuk Tonase
            $('#prefixSymbol').hide();
            $('#suffixSymbol').show();
            $('#nilaiInput').removeClass('border-start-0').addClass('border-end-0');
            // Hapus border kiri karena tidak ada prefix, tapi hilangkan border kanan agar nempel dengan suffix
            $('#nilaiInput').addClass('border-start').removeClass('border-start-0');
        } else {
            // Setup UI untuk Rupiah
            $('#prefixSymbol').show();
            $('#suffixSymbol').hide();
            // Hilangkan border kiri agar nempel dengan prefix, beri border kanan
            $('#nilaiInput').addClass('border-start-0').removeClass('border-end-0 border-start');
        }

        // --- SELALU JALANKAN CLEAVE MASKING ---
        // Hancurkan instance Cleave yang lama sebelum membuat baru agar tidak menumpuk
        if (cleaveInstance) {
            cleaveInstance.destroy();
        }

        // Inisialisasi Cleave baru (Berlaku untuk Rupiah maupun Tonase)
        cleaveInstance = new Cleave('#nilaiInput', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });
    }

    // Submit konfirmasi untuk Batasan Umum
    $('#editBatasanForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Nilai batasan ini akan segera diperbarui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Alert auto close
    $("#success-alert").fadeTo(5000, 500).slideUp(500);
</script>
@endpush
