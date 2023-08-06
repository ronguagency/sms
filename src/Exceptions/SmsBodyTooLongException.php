<?php

namespace Rongu\Sms\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SmsBodyTooLongException extends InvalidArgumentException
{
    public static function create()
    {
        
        $maxLength = (new static)->getMaxLength();
        return new static("Sms Text too long, consider reducing to $maxLength characters.");
    }

    private function getMaxLength() {
        $maxLength = config('sms.multi_sms_max_length') * config('sms.multi_sms_max_messages');
        $wordWrapAdjust = config('sms.multi_sms_max_messages') * 10;
        $adjustedMaxLength = $maxLength - $wordWrapAdjust;
        $roundedAdjustedMaxLength = $adjustedMaxLength - ($adjustedMaxLength % 10);
        return $roundedAdjustedMaxLength;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
