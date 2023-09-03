@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BILLING</u></h1>
        </div>
    </div>
    {{-- if session has success, trigger sweet alert --}}
    @if (session('success'))
    <script>
        Swal.fire(
                'Berhasil!',
                '{{session('success')}}',
                'success'
            )
    </script>
    @endif
    <div class="row justify-content-left">
        @if (auth()->user()->role === 'admin')
        <div class="col-md-4 text-center mt-5">
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="formKasBesar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <select class="form-select form-select-lg" name="" id="tipeKasBesar">
                                    <option value="masuk">Masuk</option>
                                    <option value="keluar">Keluar</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary"
                                onclick="tipeFormKasBesar()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasBesar">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="100">
                <h2>FORM KAS BESAR</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <!-- Modal trigger button -->
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasKecil">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="100">
                <h2>FORM KAS KECIL</h2>
            </a>
            <!-- Modal Body -->
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="formKasKecil" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <select class="form-select form-select-lg" name="" id="tipeKasKecil">
                                    <option value="masuk">Masuk</option>
                                    <option value="keluar">Keluar</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <a href="#" class="btn btn-primary" onclick="tipeFormKasKecil()">Lanjutkan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    function tipeFormKasBesar() {
            let tipeKasBesar = document.getElementById('tipeKasBesar').value;
            if (tipeKasBesar === 'masuk') {
                window.location.href = "{{route('kas-besar.masuk')}}";
            } else if (tipeKasBesar === 'keluar') {
                window.location.href = "{{route('kas-besar.keluar')}}";
            }
        }
        function tipeFormKasKecil() {
            let val = document.getElementById('tipeKasKecil').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-kecil.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-kecil.keluar')}}";
            }
        }
</script>

@endpush
