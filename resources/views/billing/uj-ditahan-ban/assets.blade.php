@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/uj-ditahan-ban.css') }}?v={{ filemtime(public_path('assets/css/uj-ditahan-ban.css')) }}">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/filter-oli-maintenance.js') }}?v={{ filemtime(public_path('assets/js/filter-oli-maintenance.js')) }}" defer></script>
<script src="{{ asset('assets/js/uj-ditahan-ban.js') }}?v={{ filemtime(public_path('assets/js/uj-ditahan-ban.js')) }}" defer></script>
@endpush
