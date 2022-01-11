<?php

namespace Rongu\Sms\Core;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Rongu\Contracts\SmsSenderInterface;

class XmlResponse extends Response
{

    public static function make($rawResponse) {
        return new self($rawResponse);
    }

    public function json($key = null, $default = null)
    {
        if (! $this->decoded) {
            $this->decoded = simplexml_load_string($this->body());
        }

        if (is_null($key)) {
            return $this->decoded;
        }

        return data_get($this->decoded, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     */
    public function object()
    {
        return simplexml_load_string($this->body());
    }

    public function toArray() {
        return json_decode(json_encode($this->object()), true);
    }

    public function getPath($path) {
        return Arr::get($this->toArray(), $path);
    }
}