<div class="row justify-content-left mt-5">
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('per-customer.nota-tagihan')}}" class="text-decoration-none" >
            <img src="{{asset('images/tagihan.svg')}}" alt="" width="70">
            <h5 class="mt-3">NOTA TAGIHAN
                @if ($tagihan > 0)
                <span class="text-danger">({{$tagihan}})</span>
                @endif
            </h5>
        </a>
    </div>
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('per-customer.invoice-tagihan')}}" class="text-decoration-none">
            <img src="{{asset('images/invoice-tagihan.svg')}}" alt="" width="70">
            <h5 class="mt-3">INVOICE TAGIHAN
                @if ($invoice > 0)
                <span class="text-danger">({{$invoice}})</span>
                @endif
            </h5>
        </a>
    </div>
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('per-customer.nota-lunas')}}" class="text-decoration-none">
            <img src="{{asset('images/nota-lunas.svg')}}" alt="" width="70">
            <h5 class="mt-3">REKAP NOTA LUNAS</h5>
        </a>
    </div>
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('per-customer.tonase-tambang')}}" class="text-decoration-none">
            <img src="{{asset('images/tonase-tambang.svg')}}" alt="" width="70">
            <h5 class="mt-3">STATISTIK TONASE</h5>
        </a>
    </div>
</div>
