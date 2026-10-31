<?php

namespace App\Models;

use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupWa extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sendWa($tujuan, $pesan)
    {
        $storeWa = PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $tujuan,
            'status' => 0,
        ]);

        $this->sendRecordedMessage($storeWa);
    }

    public function sendRecordedMessage(PesanWa $message): bool
    {
        $sent = (new StarSender($message->tujuan, $message->pesan))->sendGroup();
        if ($sent === true) {
            $message->update(['status' => 1]);

            return true;
        }

        return false;
    }
}
