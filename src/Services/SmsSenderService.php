<?php

namespace Rongu\Sms\Services;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;
use Rongu\Sms\Dhiraagu\SmsXmlPostResponse;
use Rongu\Sms\Dhiraagu\XmlPostBasedSender;
use Rongu\Sms\Jobs\SmsStatusCheckJob;
use Rongu\Sms\Sms;

class SmsSenderService
{
    public function __construct(public Sms $sms) {

    }

    private function isDuplicateSms() {
        $smsMobileNo = $this->sms->mobileNo->__toString();
        $smsText = $this->sms->body;
        $isDuplicate = $this->sms->smsNotificationModel->where('mobile_no', $smsMobileNo)->where('sms_text', $smsText)->exists();
        if($isDuplicate) {
            Log::error(json_encode([
                'isDuplicate'=> $isDuplicate,
                'smsMobileNo' => $smsMobileNo,
                'smsText' => $smsText,
            ]));
        }
        return $isDuplicate;
    }

    public function send()
    {
        if($this->isDuplicateSms()) {
            return;
        }
        
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