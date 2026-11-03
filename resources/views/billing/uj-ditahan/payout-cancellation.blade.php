@if(in_array(auth()->user()?->role, ['admin', 'su'], true))
<form id="payout-cancellation-form" method="POST" hidden>@csrf<input type="hidden" name="reason"></form>
@push('js')
<script src="{{ asset('assets/js/uj-ditahan-payout-cancellation.js') }}"></script>
@endpush
@endif
