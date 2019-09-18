<?php

namespace Kotoyuuko\UstbElearning\Client;

class Direct implements Client
{
    protected $client;
    protected $cookies;

    public function __construct(array $options = [])
    {
        $defaults = [
            'timeout' => 30.0,
        ];

        $options = $options + $defaults;

        $this->cookies = new \GuzzleHttp\Cookie\CookieJar();
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'http://elearning.ustb.edu.cn/choose_courses/',
            'timeout' => $options['timeout'],
            'cookies' => $this->cookies,
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }
}
