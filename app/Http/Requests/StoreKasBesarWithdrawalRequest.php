<?php

namespace App\Http\Requests;

class StoreKasBesarWithdrawalRequest extends StoreKasBesarDepositRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'su'], true);
    }
}
