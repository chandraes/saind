<div class="row justify-content-left mt-5">
    <div class="col-md-3 text-center mb-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#vendorModal">
            <img src="{{asset('images/kas-vendor.svg')}}" alt="" width="100">
            <h2>Kas Vendor</h2>
        </a>
        <div class="modal fade" id="vendorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="vendorModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-l" role="document">
                <div class="modal-content">
                    <form action="{{route('operasional.kas-vendor')}}" method="get">
                        <div class="modal-header">
                            <h5 class="modal-title" id="vendorModalTitle">
                                Pilih Vendor
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <select class="form-select" name="vendor_id" id="vendor_id">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($vendor as $v)
                                    <option value="{{$v->id}}">{{$v->nama}}</option>
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
        <a href="{{route('operasional.perform-unit')}}" class="text-decoration-none">
            <img src="{{asset('images/perform-unit.svg')}}" alt="" width="100">
            <h2>Perform Unit</h2>
        </a>
    </div>
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('operasional.statistik-vendor')}}" class="text-decoration-none">
            <img src="{{asset('images/statistik-vendor.svg')}}" alt="" width="100">
            <h2>Statistik Vendor</h2>
        </a>
    </div>
</div>
