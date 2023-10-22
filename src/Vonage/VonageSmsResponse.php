<?php

namespace Rongu\Sms\Vonage;

use Illuminate\Http\Client\Response;

class VonageSmsResponse extends Response
{

    public static function make($rawResponse)
    {
        return new self($rawResponse);
    }

    public function content()
    {
        return $this->current();
    }

    public function messageId()
    {
        return (string) $this->content()->getMessageId();
    }

    public function messageCode()
    {
        return VonageResultCode::tryFrom((string) $this->content()->getStatus());
    }

    public function messageCodeOk()
    {
        return $this->messageCode() == VonageResultCode::DELIVERED;
    }
}
