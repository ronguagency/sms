<?php

namespace Rongu\Sms\Dhiraagu;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsStatusChecker
{
    const CODE_DELIVERED_TO_HEADSET = 2501;
    const CODE_DELIVERY_FAILED = 4591;
    private $messageId;
    private $messageKey;
    private $resp;
    private $smsNotification;
    
    public function check($smsNotification)
    {
        $this->smsNotification = $smsNotification;
        $this->messageId = $smsNotification->message_id;
        $this->messageKey = $smsNotification->message_key;

        if($this->messageId == 0) {
            return;
        }
        $this->resp = $this->send();
        Log::debug($this->resp);

        if($this->messageWasDelivered()) {
            $this->updateDelivery();
            return;
        }

        if($this->messageDeliveryFailed()) {
            $this->updateDeliveryFailure();
            return;
        }

        $this->retry();
    }

    private function retry() {
        $retries = $this->smsNotification->retries;
        $this->smsNotification->update([
            'retries' => $retries + 1,
        ]);
        if($retries < 3) {
            throw new Exception("Unidentified status we will retry");
        } else {
            $this->smsNotification->update([
                'abandoned_at' => Carbon::now(),
                'abandoned_reason' => 'No updates after trying 3 times',
            ]);
        }
    }

    private function messageDeliveryCode() {
        
        return $this->resp->getPath('TELEMESSAGE_CONTENT.MESSAGE_STATUS.STATUS_ID');
    }

    private function messageStatusDate() {
        return Carbon::parse(
            $this->resp->getPath(
                'TELEMESSAGE_CONTENT.MESSAGE_STATUS.RECIPIENT_STATUS.DEVICE.STATUS_DATE'
                )
            )->addHours(5);
    }

    private function messageStatusDescriptoin() {
        return $this->resp->getPath('TELEMESSAGE_CONTENT.MESSAGE_STATUS.RECIPIENT_STATUS.DEVICE.DESCRIPTION');
    }

    private function messageWasDelivered() {
        Log::info('message delevery/failed code: '.json_encode($this->messageDeliveryCode()));
        return ($this->messageDeliveryCode() < 3000);
    }

    private function updateDelivery() {
        $this->smsNotification->update([
            'delivered_status_at' => $this->messageStatusDate(),
        ]);
    }

    private function messageDeliveryFailed() {
        Log::info('message delevery/failed code: '.json_encode($this->messageDeliveryCode()));
        return ($this->messageDeliveryCode() >= 4000 );
    }

    private function updateDeliveryFailure() {
        
        $this->smsNotification->update([
            'abandoned_at' => $this->messageStatusDate(),
            'abandoned_reason' => $this->messageStatusDescriptoin(),
        ]);
    }

    public function xmlBody()
    {
        return view('sms::sms_status_check_xml')->with([
            'messageId' => $this->messageId,
            'messageKey' => $this->messageKey,
        ])->render();
    }

    public function send()
    {
        if($this->messageId == 0) {
            return null;
        }
        return SmsXmlPostResponse::make(
            Http::withBody(
                $this->xmlBody(), 'text/xml'
            )->post(Config::get('sms.smsXmlPostUrl'))
        );
    }

}