<?php

namespace Rongu\Sms\Contracts;


interface SmsSenderInterface
{
    public function send(string $mobileNo, string $smsText);

}