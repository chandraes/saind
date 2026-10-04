<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FilterOliLimitException extends ValidationException
{
    /** @param array<int, array{category: string, ritase: ?float, limit: int, reason: string}> $issues */
    public function __construct(public readonly array $issues, string $message)
    {
        $validator = Validator::make([], []);
        $validator->errors()->add('vehicle_id', $message);
        parent::__construct($validator);
    }
}
