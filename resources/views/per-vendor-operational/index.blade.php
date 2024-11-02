
    {{-- <div class="row justify-content-left mt-5">
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('vendor-operational.kas-per-vendor.index', auth()->user()->vendor_id)}}" class="text-decoration-none">
                <img src="{{asset('images/kas-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">Kas Vendor</h4>
            </a>
        </div>

        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('statistik-pervendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">Statistik Vendor</h4>
            </a>
        </div>
    </div> --}}
    <div class="row justify-content-left mt-3">
        {{-- <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="70">
                <h4 class="mt-3">DATABASE<br>UPAH GENDONG</h4>
            </a>
        </div> --}}
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('vendor-operational.perform-unit-pervendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit.svg')}}" alt="" width="70">
                <h4 class="mt-3">PERFORM UNIT</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
                <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="70">
                <h4 class="mt-3">STATISTIK<br>UPAH GENDONG</h4>
            </a>
            <div class="modal fade" id="upahGendongId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ugTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ugTitleId">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('vendor-operational.per-vendor.upah-gendong')}}" method="get">
                            <div class="modal-body">
                                <div class="col-md-12 mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_id">
                                        @foreach ($ug as $d)
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
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#maintenaceModal">
                <img src="{{asset('images/rekap-maintenance.svg')}}" alt="" width="70">
                <h4 class="mt-3">MAINTENANCE VEHICLE</h4>
            </a>

            <div class="modal fade" id="maintenaceModal" tabindex="-1" data-bs-backdrop="static"
                data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">
                                Pilih Vehicle
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('vendor-operational.per-vendor.maintenance-vehicle')}}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_id">
                                        @foreach ($maintenance as $m)
                                        <option value="{{$m->vehicle_id}}">{{$m->vehicle->nomor_lambung}}</option>
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
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ban_luar">
                <img src="{{asset('images/db-ban.svg')}}" alt="" width="70">
                <h4 class="mt-3">BAN LUAR</h4>
            </a>
            <div class="modal fade" id="ban_luar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ban-luarTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ban-luarTitle">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('vendor-operational.per-vendor.ban-luar')}}" method="get">
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
        </div>
    </div>
    {{-- <div class="row justify-content-left mt-5">
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="70">
                <h4 class="mt-3">DATABASE<br>BAN LUAR</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
                <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="70">
                <h4 class="mt-3">STATISTIK<br>BAN LUAR</h4>
            </a>
            <div class="modal fade" id="upahGendongId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ugTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ugTitleId">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('per-vendor.upah-gendong')}}" method="get">
                            <div class="modal-body">
                                <div class="col-md-12 mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_id">
                                        @foreach ($ug as $d)
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
    </div> --}}

