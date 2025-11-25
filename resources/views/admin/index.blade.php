
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-besar.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kas Besar</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('admin.settings.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>Setting APP</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kas Vendor</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kasbon Direksi</h2>
            </a>
        </div>
        @endif
    </div>
