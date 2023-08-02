<?php

namespace Rongu\Sms\Dhiraagu;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Sms\Contracts\SmsSenderInterface;
use Rongu\Sms\Core\XmlResponse;

class XmlPostBasedSender implements SmsSenderInterface
{
    private $smsCharLimit = 160;
    private $smsCharLimitInd = "[smsCharLimitInd]";

    public function send(string $mobileNo, string $smsText)
    {
        return SmsXmlPostResponse::make(
            Http::withBody(
                $this->smsXmlPostBody($mobileNo, $smsText), 'text/xml'
            )->post(Config::get('sms.smsXmlPostUrl'))
        );
    }

    private function smsXmlPostBody($mobileNo, $smsText) {
        return view('sms::sms_send_xml')->with([
            'user' => Config::get('sms.userId'),
            'passwd' => Config::get('sms.password'),
            'smsTexts' => $this->chunkAndFormatSmsText($smsText),
            'mobileNo' => $mobileNo,
        ])->render();
    }

    private function chunkAndFormatSmsText($smsText)
    {
        $smsText = $this->replaceNewLineChars($smsText);
        return explode(
            $this->smsCharLimitInd, 
            wordwrap($smsText, $this->smsCharLimit ,$this->smsCharLimitInd)
        );
    }

    private function replaceNewLineChars($smsText) {
        return str_replace('\r', '&#xA;&#xD;', $smsText);

    }
}