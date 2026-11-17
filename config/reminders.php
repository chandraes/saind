<?php

return [
    // Jadwal rekap Dashboard Pengingat (reminders:refresh):
    // 'hourly' = tiap 1 jam (sementara); 'daily_0800' = sekali sehari jam 08:00 (rencana produksi).
    'refresh' => env('REMINDERS_REFRESH', 'hourly'),

    // Menu Pengingat di dashboard kartu menu (vendor, operasional, eksekutif, HRD):
    // 'button' = tombol panjang satu baris paling atas; 'card' = seksi "Pengingat" berisi kartu menu.
    // Dashboard tile klasik (admin, su, user) tidak terpengaruh.
    'dashboard_entry' => env('REMINDERS_DASHBOARD_ENTRY', 'button'),
];
