<?php

namespace Rongu\Sms\Core;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Contracts\SmsSenderInterface;
use Illuminate\Support\Str;

class MvMobileNumber extends Response
{
    private $cleanMobileNum = 0;
    private $isValid = false;

    public function __construct(public string $rawMobileNum) {
        $this->init($rawMobileNum);
    }

    private function init($rawMobileNum) {
        if(Str::length($rawMobileNum) < 7) {
            return $this->setInvalid();
        }

        $cleanMobileNum = preg_replace('/[^0-9]/', '', $rawMobileNum);
        $pattern = "/^(960|00960|)(7|9)\d{6}$/i";
        if(preg_match($pattern, $cleanMobileNum)) {
            $this->isValid = true;
            $this->cleanMobileNum = $cleanMobileNum;
            return true;
        } else {
            return $this->setInvalid();
        }

    }

    private function setInvalid() {
        $this->isValid = false;
        $this->cleanMobileNum = 0;
        return false;
        
    }

    public function isValid() {
        return $this->isValid;
    }

    public function __toString()
    {
        return (string) $this->cleanMobileNum;
    }


}