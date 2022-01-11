<?php
namespace Rongu\Sms;

use Closure;
use Rongu\Sms\Core\MvMobileNumber;
use Rongu\Sms\Services\SmsSenderService;

class Sms
{
    public $smsNotificationModel;
    public $smsNotificationModelId;
    public $shoudCheckDelivery = false;
    public $deliveryCheckDelaySeconds = 30;
    public $mobileNo;
    public $body;
    public $model;
    public $includeCreateParams;
    public $successClosure;
    public $failureClosure;

    public function send() : Sms
    {
        (new SmsSenderService($this))->send();
        return $this;
    }

    public function to(int $mobileNo) : Sms
    {
        $this->mobileNo = new MvMobileNumber($mobileNo);
        return $this;
    }

    public function body(string $body) : Sms
    {
        $this->body = $body;
        return $this;
    }

    public function model(object $smsNotificationModel) : Sms
    {
        $this->smsNotificationModel = $smsNotificationModel;
        return $this;
    }

    public function includeCreateParams(array $includeCreateParams) : Sms
    {
        $this->includeCreateParams = $includeCreateParams;
        return $this;
    }

    public function successClosure() {
        if(empty($this->successClosure)) {
            return function($resp) {};
        }
        return $this->successClosure;
    }

    public function failureClosure() {
        if(empty($this->failureClosure)) {
            return function($resp) {};
        }
        return $this->failureClosure;
    }

    public function success(Closure $successClosure) : Sms
    {
        $this->successClosure = $successClosure;
        return $this;
    }

    public function failure(Closure $failureClosure) : Sms
    {
        $this->failureClosure = $failureClosure;
        return $this;
    }

    public function checkDelivery(int $deliveryCheckDelaySeconds = null) : Sms
    {
        $this->shoudCheckDelivery = true;
        if(isset($deliveryCheckDelaySeconds)) {
            $this->deliveryCheckDelaySeconds = $deliveryCheckDelaySeconds;
        }
        return $this;
    }





    

    



    
}