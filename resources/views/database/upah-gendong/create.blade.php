<button type="button" class="btn btn-success upah-primary" data-bs-toggle="modal" data-bs-target="#tambahSponsorId">
    <i class="fa fa-plus me-2" aria-hidden="true"></i> Aktifkan Upah Gendong
</button>

<div class="modal fade" id="tambahSponsorId" tabindex="-1" aria-labelledby="upah-activate-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <form action="{{ route('database.upah-gendong.store') }}" method="post" id="masukForm" class="modal-content upah-modal upah-activate">
            @csrf
            <div class="modal-header align-items-start">
                <div class="d-flex gap-3 align-items-center">
                    <span class="upah-icon flex-shrink-0"><i class="fa fa-truck" aria-hidden="true"></i></span>
                    <h2 class="modal-title" id="upah-activate-title">Aktifkan Upah Gendong</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <section class="upah-activate-section" aria-labelledby="upah-vehicle-title">
                    <h3 id="upah-vehicle-title">Kendaraan dan tarif</h3>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="vehicle_id" class="form-label">No. Lambung</label>
                            <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                                <option value="">Pilih nomor lambung</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>{{ $vehicle->nomor_lambung }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="nominal" class="form-label">Tarif</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" @class(['form-control', 'is-invalid' => $errors->has('nominal')]) id="nominal" inputmode="numeric" data-upah-money required value="{{ old('nominal') }}">
                            </div>
                            <input type="hidden" name="nominal" data-upah-canonical value="{{ old('nominal') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="tonase_min" class="form-label">Tonase minimum</label>
                            <div class="input-group">
                                <input type="text" @class(['form-control', 'is-invalid' => $errors->has('tonase_min')]) name="tonase_min" id="tonase_min" inputmode="decimal" aria-describedby="upah-tonase-help" required value="{{ old('tonase_min') }}">
                                <span class="input-group-text">ton</span>
                            </div>
                            <div id="upah-tonase-help" class="form-text">Desimal memakai titik.</div>
                        </div>
                    </div>
                </section>

                <section class="upah-activate-section" aria-labelledby="upah-personnel-title">
                    <h3 id="upah-personnel-title">Driver dan pengurus</h3>
                    <div class="row g-3">
                        @foreach (['driver', 'pengurus'] as $person)
                            <div class="col-12 col-sm-7">
                                <label for="nama_{{ $person }}" class="form-label">Nama {{ $person }}</label>
                                <input type="text" class="form-control" name="nama_{{ $person }}" id="nama_{{ $person }}" maxlength="255" required value="{{ old('nama_'.$person) }}">
                            </div>
                            <div class="col-12 col-sm-5">
                                <label for="tanggal_masuk_{{ $person }}" class="form-label">Tanggal masuk {{ $person }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    <input type="text" class="form-control tanggal_flatpick" name="tanggal_masuk_{{ $person }}" id="tanggal_masuk_{{ $person }}" placeholder="DD-MM-YYYY" required readonly value="{{ old('tanggal_masuk_'.$person) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="upah-activate-section" aria-labelledby="upah-account-title">
                    <h3 id="upah-account-title">Rekening pembayaran</h3>
                    <div class="row g-3">
                        @foreach (['bank' => 'Nama bank', 'no_rek' => 'Nomor rekening', 'nama_rek' => 'Nama pemilik rekening'] as $field => $label)
                            <div class="col-12 col-md-4">
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <input type="text" class="form-control" name="{{ $field }}" id="{{ $field }}" @if ($field === 'no_rek') inputmode="numeric" @endif required value="{{ old($field) }}">
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary upah-primary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success upah-primary">
                    <i class="fa fa-check me-2" aria-hidden="true"></i> Aktifkan
                </button>
            </div>
        </form>
    </div>
</div>
