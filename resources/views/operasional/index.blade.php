<div class="row justify-content-left mt-5">
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('operasional.perform-unit')}}" class="text-decoration-none">
            <img src="{{asset('images/perform-unit.svg')}}" alt="" width="80">
            <h4 class="mt-3">Perform Unit</h4>
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
</div>
