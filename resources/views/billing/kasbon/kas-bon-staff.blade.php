@extends('layouts.app')
@section('content')
<div class="container py-3" style="max-width:1000px">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><div class="small text-success fw-bold text-uppercase mb-1">Billing · Pinjaman Pegawai</div><h2 class="fw-bold mb-1">Kasbon Staff</h2><p class="text-muted mb-0">Pilih metode pembayaran pinjaman sesuai kebutuhan pegawai.</p></div><a href="{{ route('billing.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Billing</a></div>
    @include('swal')
    <div class="row g-4 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="text-success fs-3 mb-3"><i class="fa fa-money"></i></div><h5 class="fw-bold">Potong Gaji</h5><p class="text-muted small">Sisa pinjaman dipotong melalui penggajian sesuai periode yang berlaku.</p><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Batas total kasbon 50%</span><p class="small text-muted mt-3 mb-0">Form tersedia mulai tanggal 16 setiap bulan.</p></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="text-success fs-3 mb-3"><i class="fa fa-calendar-check-o"></i></div><h5 class="fw-bold">Cicilan</h5><p class="text-muted small">Atur angsuran bulanan dan periode mulai pemotongan melalui gaji.</p><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Batas angsuran bulanan 30%</span><p class="small text-muted mt-3 mb-0">Pegawai harus tidak memiliki cicilan belum lunas.</p></div></div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><form id="method-form"><label for="metodeSelect" class="form-label fw-bold">Metode kasbon</label><div class="row g-3 align-items-end"><div class="col-md-8"><select class="form-select" id="metodeSelect" required><option value="{{ route('billing.kasbon.index') }}">Potong Gaji</option>@if(in_array(auth()->user()->role, ['admin', 'su'], true))<option value="{{ route('billing.kasbon.kas-bon-cicil') }}">Cicilan</option>@endif</select></div><div class="col-md-4"><button type="submit" class="btn btn-success w-100">Lanjutkan <i class="fa fa-arrow-right ms-1"></i></button></div></div><p class="small text-muted mt-3 mb-0"><i class="fa fa-info-circle me-1"></i> Persentase dihitung dari gaji pokok + tunjangan jabatan + tunjangan keluarga.</p></form></div></div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
$(function(){
    $('#metodeSelect').select2({theme:'bootstrap-5',width:'100%',minimumResultsForSearch:Infinity});
    $('#method-form').on('submit',function(event){event.preventDefault();window.location.assign($('#metodeSelect').val());});
});
</script>
@endpush
