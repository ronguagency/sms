<?php

namespace Rongu\Sms\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InvalidMobileNumberException extends InvalidArgumentException
{
    public static function create($mobileNumber)
    {
        return new static("The mobile number `{$mobileNumber}` is invalid.");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
