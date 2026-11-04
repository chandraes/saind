<?php

namespace App\Http\Requests;

class StoreScannedLoadNoteRequest extends InspectLoadNoteRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [...parent::rules(), 'request_id' => ['required', 'uuid'],
            'confirmed_vehicle_id' => ['required', 'integer', 'min:1'],
            'photo' => StoreLoadNoteDraftRequest::photoRules()];
    }
}
