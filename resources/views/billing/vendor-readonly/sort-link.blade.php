{{-- Judul kolom yang bisa diurutkan: klik pertama naik (A-Z / terlama), klik berikutnya membalik arah. --}}
@php
    $sortKey = $key ?? null;
    $active = $sortKey !== null && request('sort') === $sortKey;
    $ascending = request('dir', 'asc') !== 'desc';
@endphp
@if ($sortKey === null)
    {{ $label }}
@else
    <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'dir' => $active && $ascending ? 'desc' : 'asc', 'page' => null]) }}"
        class="text-reset text-decoration-none text-nowrap vendor-readonly-sort" data-sort="{{ $sortKey }}"
        @if ($active) aria-sort="{{ $ascending ? 'ascending' : 'descending' }}" @endif
        title="Urutkan menurut {{ $label }}">{{ $label }} <i class="fa {{ $active ? ($ascending ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort text-muted' }}" aria-hidden="true"></i></a>
@endif
