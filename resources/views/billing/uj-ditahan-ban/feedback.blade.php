@if ($errors->any() || session('error') || session('success'))
<div hidden data-maintenance-notification data-icon="{{ $errors->any() || session('error') ? 'error' : (session('warning') ? 'warning' : 'success') }}" data-message="{{ $errors->any() ? implode(' ', $errors->all()) : (session('error') ?: session('success').' '.session('warning')) }}"></div>
@endif
