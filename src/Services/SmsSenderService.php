<?php

namespace Rongu\Sms\Services;

use Carbon\Carbon;
use Closure;
use Rongu\Sms\Dhiraagu\SmsXmlPostResponse;
use Rongu\Sms\Dhiraagu\XmlPostBasedSender;
use Rongu\Sms\Jobs\SmsStatusCheckJob;
use Rongu\Sms\Sms;

class SmsSenderService
{
    public function __construct(public Sms $sms) {

    }

    public function send()
    {
        
        $smsNotification = $this->storeSmsNotification();

        if( ! $this->sms->mobileNo->isValid()) {
            $this->abondonMessageDueToInvalidMobileNumber($smsNotification);
        }

        $resp = $this->sendViaProvider($this->sms);

        if($resp->successful()) {
            $this->onSuccess($resp, $smsNotification);
        }

        if($this->sms->shoudCheckDelivery) {
            dispatch(new SmsStatusCheckJob($smsNotification));
        }
    }

    private function sendViaProvider($sms) {
        return (new XmlPostBasedSender)->send($sms->mobileNo->__toString(), $sms->body);
    }

    private function storeSmsNotification() {

        return $this->sms->smsNotificationModel->create(
            array_merge([
                'mobile_no' => (string) $this->sms->mobileNo,
                'sms_text' => $this->sms->body,
            ], $this->sms->includeCreateParams)
        );
    }

    private function abondonMessageDueToInvalidMobileNumber($smsNotification) {
            $smsNotification->abandoned_at = Carbon::now();
            $smsNotification->abandoned_reason = "Invalid mobile number";
            $smsNotification->save();
            return;
    }

    private function onSuccess($resp, $smsNotification) {
        $smsResponse = SmsXmlPostResponse::make($resp);
        $smsNotification->message_id = $smsResponse->messageId();
        $smsNotification->message_key = $smsResponse->messageKey();
        $smsNotification->sent_status_at = Carbon::now();
        $smsNotification->save();

        $this->sms->successClosure($resp);
    }



    
}