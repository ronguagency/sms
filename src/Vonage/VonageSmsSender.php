<?php

namespace Rongu\Sms\Vonage;

use Illuminate\Support\Facades\Config;
use Rongu\Sms\Contracts\SmsSenderInterface;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class VonageSmsSender implements SmsSenderInterface {
    private $clientRef = 'sms';

    public function setClientRef($clientRef) {
        $this->clientRef = $clientRef;
        return $this;
    }

    public function send(string $mobileNumber, string $smsBody)
    {
        $basic = new Basic(Config::get('sms.vonage.key'), Config::get('sms.vonage.secret'));
        $client = new Client($basic);
    
        $text = new SMS($mobileNumber, Config::get('sms.sender_alias'), $smsBody);
        $text->setClientRef($this->clientRef);
    
        return VonageSmsResponse::make(
            $client->sms()->send($text)
        );
    }
}