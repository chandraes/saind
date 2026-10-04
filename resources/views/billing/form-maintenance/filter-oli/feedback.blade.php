@if ($errors->any() || session('error') || session('success'))
    <div hidden data-maintenance-notification data-icon="{{ $errors->any() || session('error') ? 'error' : 'success' }}" data-message="{{ $errors->any() ? implode(' ', $errors->all()) : (session('error') ?: session('success')) }}"></div>
@endif
@pushOnce('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
@endPushOnce
@pushOnce('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/filter-oli-maintenance.js') }}" defer></script>
@endPushOnce
