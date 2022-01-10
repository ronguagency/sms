<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class SmsNotificationViaDhiraaguService
{
    private $userId;
    private $password;
    private $smsUrl;

    public function __construct()
    {
        $this->userId = Config::get('sms.userId');
        $this->password = Config::get('sms.password');
        $this->smsUrl = Config::get('sms.smsUrl');
        
    }

    private function http() {
        return new GuzzleHttpClient();
    }
    public function send(int $mobileNo, string $smsText)
    {
        $response = $this->http()->request(
            'GET', 
            $this->smsUrl,
            [
                'query' => $this->getPreparedMessage($mobileNo, $smsText)
            ]
        );
        return $response->getStatusCode();
    }

    public function sendViaXmlPost(int $mobileNo, string $smsText)
    {
        return Http::withBody(
            $this->smsXmlPostBody($mobileNo, $smsText), 'text/xml'
        )->post(Config::get('sms.smsXmlPostUrl'));
    }

    private function prepareMessage($mobileNo, $smsText){
        return [
            'userid' => $this->userId,
            'password' => $this->password,
            'to' => '960' . $mobileNo,
            'text' => $smsText
        ];
    }
    private function getPreparedMessage($mobileNo, $smsText)
    {
        return $this->prepareMessage($mobileNo, $smsText);
    }

    private function smsXmlPostBody($mobileNo, $smsText) {
        return view('sms_send_xml')->with([
            'user' => Config::get('sms.userId'),
            'passwd' => Config::get('sms.password'),
            'index' => 0,
            'smsText' => $smsText,
            'mobileNo' => $mobileNo,
        ])->render();
    }
}