{{-- Filter vendor untuk role operasional (melihat seluruh vendor). --}}
@if (($vendors ?? collect())->isNotEmpty())
<div class="{{ $class ?? 'col-md-3' }}">
    <label class="form-label" for="vendor_id">Vendor</label>
    <select id="vendor_id" name="vendor_id" class="form-select readonly-select">
        <option value="">Seluruh vendor</option>
        @foreach ($vendors as $option)
        <option value="{{ $option->id }}" @selected((string) request('vendor_id') === (string) $option->id)>{{ $option->nama }}@if ($option->nickname) ({{ $option->nickname }})@endif</option>
        @endforeach
    </select>
</div>
@endif
