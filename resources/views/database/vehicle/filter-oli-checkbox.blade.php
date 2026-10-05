<div class="d-inline-flex align-items-center gap-2">
    <input type="checkbox" class="form-check-input m-0 vehicle-filter-oli-restriction" aria-label="Limit Filter & Oli Mesin kendaraan {{ $vehicle->nomor_lambung }}" id="filterOliRestriction{{ $vehicle->id }}" @checked($vehicle->pembatasan_filter_oli) data-saved="{{ $vehicle->pembatasan_filter_oli ? '1' : '0' }}" data-url="{{ route('vehicle.pembatasan-filter-oli.update', $vehicle->id) }}" data-token="{{ csrf_token() }}" data-vehicle="{{ $vehicle->nomor_lambung }}">
</div>
