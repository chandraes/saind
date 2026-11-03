<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoadNoteRuleException extends ValidationException
{
    public function __construct(string $field, string $message, public readonly string $reasonCode, public readonly string $nextAction)
    {
        parent::__construct(Validator::make([], []));
        $this->validator->errors()->add($field, $message);
    }

    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return response()->json(['message' => $this->validator->errors()->first(), 'errors' => $this->errors(),
            'decision' => ['accepted' => false, 'code' => $this->reasonCode, 'action' => $this->nextAction]], $this->status);
    }
}
