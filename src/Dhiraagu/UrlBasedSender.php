<?php

namespace Rongu\Sms\Dhiraagu;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Sms\Contracts\SmsSenderInterface;

class UrlBasedSender implements SmsSenderInterface
{

    public function send(string $mobileNo, string $smsText)
    {
        return Http::get(
            Config::get('sms.smsUrl'), 
            $this->queryParams($mobileNo, $smsText)
        );
    }


    private function queryParams($mobileNo, $smsText){
        return [
            'userid' => Config::get('sms.userId'),
            'password' => Config::get('sms.password'),
            'to' => '960' . $mobileNo,
            'text' => $smsText
        ];
    }
}