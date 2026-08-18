<div class="modal fade" id="modalShow{{$d->id}}" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-truck me-2"></i> Detail Kendaraan: {{$d->nomor_lambung}}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold"><i class="fa fa-info-circle me-2"></i> Informasi Umum</h6>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">VENDOR</div>
                            <div class="col-8 fw-bold">{{$d->vendor->nama}} ({{$d->vendor->perusahaan}})</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">NOMOR LAMBUNG</div>
                            <div class="col-8 fw-bold"><span class="badge bg-dark">{{$d->nomor_lambung}}</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">NOMOR POLISI</div>
                            <div class="col-8 fw-bold">{{$d->nopol}}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">NAMA STNK</div>
                            <div class="col-8 fw-bold">{{$d->nama_stnk}}</div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold mt-4"><i class="fa fa-cogs me-2"></i> Spesifikasi Teknis</h6>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">NO RANGKA</div>
                            <div class="col-8 fw-bold">{{$d->no_rangka}}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">NO MESIN</div>
                            <div class="col-8 fw-bold">{{$d->no_mesin}}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">TIPE / TAHUN</div>
                            <div class="col-8 fw-bold">{{$d->tipe}} / {{$d->tahun}}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold"><i class="fa fa-calendar-check me-2"></i> Masa Berlaku Dokumen</h6>
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block">Pajak STNK</small>
                                    <span class="fw-bold">{{$d->id_tanggal_pajak_stnk}}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block">KIR</small>
                                    <span class="fw-bold">{{$d->id_tanggal_kir}}</span>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold mt-4"><i class="fa fa-university me-2"></i> Rekening Uang Jalan</h6>
                        <div class="p-3 border rounded border-success bg-light">
                            <div class="mb-1 text-muted small">BANK / NOMOR REKENING</div>
                            <div class="h5 fw-bold mb-1 text-success">{{$d->bank}} - {{$d->no_rekening}}</div>
                            <div class="small">A.N: <strong>{{$d->transfer_ke}}</strong></div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="badge p-3 {{ $d->gps == 1 ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fa fa-map-marker-alt me-1"></i> GPS: {{ $d->gps == 1 ? 'AKTIF' : 'OFF' }}
                                </div>
                                <div class="badge p-3 bg-info">
                                    <i class="fa fa-hashtag me-1"></i> INDEX: {{$d->no_index}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4 btn-edit-vehicle" data-bs-dismiss="modal" data-id="{{$d->id}}">
                    <i class="fa fa-edit me-1"></i> Edit Data
                </button>
            </div>
        </div>
    </div>
</div>
