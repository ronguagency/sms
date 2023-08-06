<?php

namespace Rongu\Sms\Dhiraagu;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Sms\Contracts\SmsSenderInterface;
use Rongu\Sms\Core\XmlResponse;

class SmsXmlPostResponse extends XmlResponse
{

    public static function make($rawResponse) {
        return new self($rawResponse);
    }

    public function content()
    {
        return $this->object()->TELEMESSAGE_CONTENT->RESPONSE;
    }

    public function messageId() {
        return (string) $this->content()->MESSAGE_ID;
    }

    public function messageKey() {
        return (string) $this->content()->MESSAGE_KEY;
    }

    public function messageCode() {
        return ResultCode::tryFrom((string) $this->content()->RESPONSE_STATUS);
    }

    public function messageCodeOk() {
        
        return $this->messageCode() == ResultCode::OK || $this->messageCode() == ResultCode::OK_ALT;
    }


}