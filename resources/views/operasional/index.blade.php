<div class="row justify-content-left mt-5">
    <div class="col-md-3 text-center mb-5">
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
                            Pilih NOLAM
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
    </div>
    @foreach ([['id' => 'operasionalAki', 'title' => 'AKI', 'icon' => 'aki.svg', 'route' => 'statistik.aki'], ['id' => 'operasionalFilterOli', 'title' => 'FILTER & OLI MESIN', 'icon' => 'filter-oli-mesin.svg', 'route' => 'statistik.filter-oli']] as $menu)
        <div class="col-md-3 text-center mb-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#{{ $menu['id'] }}">
                <img src="{{ asset('images/'.$menu['icon']) }}" alt="" width="80">
                <h5 class="mt-3">{{ $menu['title'] }}</h5>
            </a>
            <div class="modal fade" id="{{ $menu['id'] }}" tabindex="-1" aria-labelledby="{{ $menu['id'] }}Title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header"><h2 class="modal-title fs-5 fw-bold" id="{{ $menu['id'] }}Title">Statistik {{ $menu['title'] }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <form action="{{ route($menu['route']) }}" method="get">
                        <div class="modal-body text-start">
                            <p class="small text-muted">Pilih kendaraan untuk melihat kondisi dan histori penggantian.</p>
                            <label for="{{ $menu['id'] }}Vehicle" class="form-label fw-semibold">Nomor lambung</label>
                            <select class="form-select operasional-maintenance-select" name="vehicle_id" id="{{ $menu['id'] }}Vehicle" required>
                                <option value="">Cari nomor lambung kendaraan</option>
                                @foreach ($vehicle as $unit)<option value="{{ $unit->id }}">{{ $unit->nomor_lambung }}</option>@endforeach
                            </select>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Lihat statistik</button></div>
                    </form>
                </div></div>
            </div>
        </div>
    @endforeach
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('statistik.perform-unit')}}" class="text-decoration-none">
            <img src="{{asset('images/perform-unit.svg')}}" alt="" width="80">
            <h4 class="mt-3">Perform Unit</h4>
        </a>
    </div>
      <div class="col-md-3 text-center mb-5">
            <a href="{{route('statistik.perform-unit.all-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/all-vendor.svg')}}" alt="" width="80">
                <h4 class="mt-3">PERFORM UNIT ALL VENDOR</h4>
            </a>
        </div>
    <div class="col-md-3 text-center mb-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
            <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="80">
            <h4 class="mt-3">UPAH GENDONG</h4>
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
                    <form action="{{route('operasional.upah-gendong')}}" method="get">
                    <div class="modal-body">
                        <div class="col-md-12 mb-3">
                            <select
                                class="form-select"
                                name="vehicle_id"
                                id="vehicle_id"
                            >
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
    <div class="col-md-3 text-center mb-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#maintenaceModal">
            <img src="{{asset('images/rekap-maintenance.svg')}}" alt="" width="80">
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
                    <form action="{{route('operasional.maintenance-vehicle')}}" method="get">
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
    <div class="col-md-3 text-center mb-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#tonaseTambang">
            <img src="{{asset('images/tonase-tambang.svg')}}" alt="" width="80">
            <h5 class="mt-3">STATISTIK TONASE</h5>
        </a>
    </div>
    <div class="modal fade" id="tonaseTambang" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Pilih Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach ($customer as $c)
                        <div class="col-md-2 text-center mt-5">
                            <a href="{{route('operasional.tonase-tambang', $c)}}" class="text-decoration-none">
                                <img src="{{asset('images/tambang.svg')}}" alt="" width="70">
                                <h4 class="mt-3">{{$c->singkatan}}

                                </h4>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="button" class="btn btn-primary">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
