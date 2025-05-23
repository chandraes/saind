<div class="container-fluid">
    <form action="{{route('per-customer-admin.nota-tagihan')}}" method="get">
        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="rute_id" class="form-label">Rute</label>
                    <select class="form-select" name="rute_id" id="rute_id">
                        <option value=""> -- Pilih Rute -- </option>
                        @foreach ($rute as $r)
                        <option value="{{$r->id}}" {{$r->id == $rute_id ? 'selected' : ''}}>{{$r->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="start_date" class="form-label">Filter Tanggal</label>
                    <select name="filter_date" id="filter_date" class="form-select">
                        <option value="" >-- Pilih Berdasarkan --</option>
                        <option value="tanggal_muat" {{$filter_date == 'tanggal_muat' ? 'selected' : ''}}>Tanggal Muat</option>
                        <option value="tanggal_bongkar" {{$filter_date == 'tanggal_bongkar' ? 'selected' : ''}}>Tanggal Bongkar</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="start_date" class="form-label">Range Tanggal</label>
                    <input type="text" class="form-control" name="tanggal_filter" id="tanggal_filter" value="{{$tanggal_filter}}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="rute_id" class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </div>
            <div class="col-md-2">
                <label for="rute_id" class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <a href="{{route('per-customer-admin.nota-tagihan')}}" class="btn btn-secondary">Reset
                        Filter</a>
                </div>
            </div>
        </div>
    </form>
</div>
