<?php

namespace Rongu\Sms\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SmsSendFailedException extends InvalidArgumentException
{
    public static function create($code, $message)
    {
        return new static("{$message}");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
