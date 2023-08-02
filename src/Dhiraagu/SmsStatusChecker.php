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
    private $invalidMessageId = false;
    
    public function check($messageId, $messageKey)
    {
        $this->messageId = $messageId;
        $this->messageKey = $messageKey;

        if($this->messageId == 0) {
            $this->invalidMessageId = true;
            Log::debug("Message id is zero, this is invalid");
            return $this;
        }
        $this->resp = $this->send();
        Log::debug($this->resp);

        return $this;
    }

    public function messageStatusId() {
        if($this->invalidMessageId) {
            return 9999; // to indicate invalid message;
        }
        
        return $this->resp->getPath('TELEMESSAGE_CONTENT.MESSAGE_STATUS.STATUS_ID');
    }

    public function messageStatusDate() {
        return Carbon::parse(
            $this->resp->getPath(
                'TELEMESSAGE_CONTENT.MESSAGE_STATUS.RECIPIENT_STATUS.DEVICE.STATUS_DATE'
                )
            )->addHours(5);
    }

    public function messageStatusDescription() {
        return $this->resp->getPath('TELEMESSAGE_CONTENT.MESSAGE_STATUS.RECIPIENT_STATUS.DEVICE.DESCRIPTION');
    }

    public function messageWasDelivered() {
        Log::info('message delevery/failed code: '.json_encode($this->messageStatusId()));
        return ($this->messageStatusId() < 3000);
    }

    public function messageDeliveryFailed() {
        Log::info('message delivery/failed code: '.json_encode($this->messageStatusId()));
        return ($this->messageStatusId() >= 4000 );
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