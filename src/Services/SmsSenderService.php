<?php

namespace Rongu\Sms\Services;

use Carbon\Carbon;
use Closure;
use Rongu\Sms\Dhiraagu\SmsXmlPostResponse;
use Rongu\Sms\Dhiraagu\XmlPostBasedSender;
use Rongu\Sms\Exceptions\InvalidMobileNumberException;
use Rongu\Sms\Exceptions\SmsSendFailedException;
use Rongu\Sms\Jobs\SmsStatusCheckJob;
use Rongu\Sms\Models\DhiSmsLog;
use Rongu\Sms\Models\VonageSmsLog;
use Rongu\Sms\Sms;
use Rongu\Sms\Vonage\VonageSmsSender;

class SmsSenderService
{
    private $response;
    private $mobileNumber;
    private $smsBody;
    private $type;
    public function __construct(public Sms $sms) {

    }

    public function send($mobileNumber, $body)
    {
        $this->type = 'Dhi';
        $this->mobileNumber = $mobileNumber = $this->cleanSms($mobileNumber);
        $this->smsBody = $body;
        $this->validateSMS($mobileNumber, $body);
        $this->response = $smsResp = app()->make(XmlPostBasedSender::class)->send($mobileNumber, $body);
        
        $smsResp->messageCodeOk() ? 
            $this->onSuccess($smsResp, $mobileNumber, $body) : 
                $this->onFailure($smsResp);
        return $this;
    }

    public function sendViaVonage($mobileNumber, $body, $clientRef = 'sms') 
    {
        $this->type = 'Vonage';
        $this->mobileNumber = $this->cleanSms($mobileNumber);
        $this->smsBody = $body;
        $this->validateForeignNumber($mobileNumber);
        $this->response = app()->make(VonageSmsSender::class)->setClientRef($clientRef)->send($mobileNumber, $body);
        return $this;
    }

    public function response() {
        return $this->response;
    }

    public function save() {
        $smsLog = null;
        if($this->type === 'Dhi') {
            $smsLog = $this->storeDhiSmsLog($this->response, $this->mobileNumber, $this->smsBody);
        }
        if($this->type === 'Vonage') {
            $smsLog = $this->storeVonageSmsLog($this->response, $this->mobileNumber, $this->smsBody);
        }
        return $smsLog;
    }

    private function validateSMS(string $mobileNumber) {
        $pattern = "/^(960|00960|)(7|9)\d{6}$/i";
        if( ! preg_match($pattern, $mobileNumber)) {
            throw InvalidMobileNumberException::create($mobileNumber);
        } 
    }

    private function validateForeignNumber(string $mobileNumber) {
        // implement
    }

    private function cleanSms(string $mobileNumber) : string {
        return preg_replace('/[^0-9]/', '', $mobileNumber);
    }

    private function storeDhiSmsLog($smsResp, $mobileNumber, $body) {      
        $dhiSmsLog = new DhiSmsLog();
        $dhiSmsLog->mobile_number = $mobileNumber;
        $dhiSmsLog->sms_body = $body;
        $dhiSmsLog->message_id = $smsResp->messageId();
        $dhiSmsLog->message_key = $smsResp->messageKey();
        $dhiSmsLog->sent_at = Carbon::now();
        $dhiSmsLog->save();
        return $dhiSmsLog;
    }
    
    
    private function storeVonageSmsLog($resp, $mobileNumber, $smsBody) {      
        $vonageSmsLog = new VonageSmsLog();
        $vonageSmsLog->mobile_number = $mobileNumber;
        $vonageSmsLog->sms_body = $smsBody;
        $vonageSmsLog->message_id = $resp->messageId();
        $vonageSmsLog->sent_at = Carbon::now();
        $vonageSmsLog->save();
        return $vonageSmsLog;
    }

    private function onSuccess($smsResp, $mobileNumber, $body) {
        if(config('sms.delivery_check_enabled')) {
            dispatch(new SmsStatusCheckJob($smsResp->messageId(), $smsResp->messageKey()));
        }
    }

    private function onFailure($smsResp) {
        throw SmsSendFailedException::create($smsResp->messageCode(), $smsResp->content()->RESPONSE_STATUS_DESC);
    }

    
}