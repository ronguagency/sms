<?php

namespace Rongu\Sms\Dhiraagu;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Sms\Contracts\SmsSenderInterface;
use Rongu\Sms\Core\XmlResponse;
use Rongu\Sms\Exceptions\SmsBodyTooLongException;

class XmlPostBasedSender implements SmsSenderInterface
{

    public function send(string $mobileNumber, string $smsBody)
    {
        return SmsXmlPostResponse::make(
            Http::withBody(
                $this->smsXmlPostBody($mobileNumber, $smsBody), 'text/xml'
            )->post(Config::get('sms.smsXmlPostUrl'))
        );
    }

    private function smsXmlPostBody($mobileNumber, $smsBody) {
        return view('sms::sms_send_xml')->with([
            'user' => Config::get('sms.userId'),
            'passwd' => Config::get('sms.password'),
            'smsBodyParts' => $this->getSmsBodyParts($smsBody),
            'mobileNumber' => $mobileNumber,
        ])->render();
    }

    // split sms into multiple parts if it is too long, to be sent as "multipart" sms
    private function getSmsBodyParts($smsBody) {
        $smsEndIdentifier = '[sms_end]';
        if(strlen($smsBody) <= Config::get('sms.single_sms_max_length')) {
            return [$smsBody];
        }
        $smsBodyParts = explode(
            $smsEndIdentifier, 
            wordwrap($smsBody, Config::get('sms.multi_sms_max_length'), $smsEndIdentifier)
        );

        if(count($smsBodyParts) > Config::get('sms.multi_sms_max_messages')) {
            throw SmsBodyTooLongException::create();
        }

        return $smsBodyParts;


    }
}