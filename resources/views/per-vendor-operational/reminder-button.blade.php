{{-- Menu Pengingat sebagai tombol panjang satu baris di atas seksi menu (lihat config reminders.dashboard_entry). --}}
{{-- Menempel di atas layar saat digulir hanya bila ada hal yang perlu diingatkan. --}}
<a class="vendor-reminder-button{{ $menu['badge'] > 0 ? ' is-sticky' : '' }}" href="{{ $menu['url'] }}" data-vendor-menu-row="reminders">
    <span class="vendor-reminder-icon"><img src="{{ asset('images/'.$menu['icon']) }}" alt="" width="40" height="40"></span>
    <span class="vendor-reminder-text">
        <strong>{{ $menu['title'] }}</strong>
        <span>{{ $menu['description'] }}</span>
    </span>
    <span class="vendor-reminder-count {{ $menu['badge'] > 0 ? '' : 'is-zero' }}" aria-label="{{ $menu['badge'] }} pengingat" data-reminder-badge>{{ $menu['badge'] }}</span>
    <span class="vendor-reminder-arrow" aria-hidden="true">→</span>
</a>
