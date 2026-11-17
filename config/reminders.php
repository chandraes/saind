<?php

return [
    // Jadwal rekap Dashboard Pengingat (reminders:refresh):
    // 'hourly' = tiap 1 jam (sementara); 'daily_0800' = sekali sehari jam 08:00 (rencana produksi).
    'refresh' => env('REMINDERS_REFRESH', 'hourly'),
];
