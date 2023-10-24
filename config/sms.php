<?php

/*
 * You can place your custom package configuration in here.
 */

return [
    'userId' => env('DHIRAAGU_USER_ID'),
    'password' => env('DHIRAAGU_PASSWORD'),
    'smsUrl' => env('DHIRAAGU_SMS_URL'),
    'smsXmlPostUrl' => env('DHIRAAGU_SMS_XML_POST_URL'),

    'log_model' => \Rongu\Sms\Models\DhiSmsLog::class,
    
    
    'single_sms_max_length' => env('SINGLE_SMS_MAX_LENGTH', 160),
    'multi_sms_max_length' => env('MULTI_SMS_MAX_LENGTH', 153),
    'multi_sms_max_messages' => env('MULTI_SMS_MAX_MESSAGES', 3),
    'sender_alias' => env('RONGU_SMS_SENDER_ALIAS'),

    'vonage' => [
        'key' => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
    ]

];