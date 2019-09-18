<?php

namespace Kotoyuuko\UstbElearning\Client;

use Kotoyuuko\UstbElearning\Exceptions\HttpException;
use Kotoyuuko\UstbElearning\Exceptions\InvalidArgumentException;
use Kotoyuuko\UstbElearning\Helpers\WengineVPN;

class Proxy implements Client
{
    protected $client;
    protected $cookies;
    protected $vpn;

    public function __construct(array $options = [])
    {
        $defaults = [
            'token' => null,
            'timeout' => 30.0,
        ];

        $options = $options + $defaults;

        if ($options['token'] === null) {
            throw new InvalidArgumentException('Invalid token for wengine vpn');
        }

        $this->buildCookies($options['token']);

        $this->vpn = new WengineVPN();

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://n.ustb.edu.cn/http/' . $this->vpn->encryptUrl('elearning.ustb.edu.cn') . '/choose_courses/',
            'timeout' => $options['timeout'],
            'cookies' => $this->cookies,
        ]);

        $this->checkToken();
    }

    private function buildCookies($token)
    {
        $this->cookies = new \GuzzleHttp\Cookie\CookieJar();

        try {
            $response = (new \GuzzleHttp\Client([
                'cookies' => $this->cookies,
            ]))->request('GET', 'https://n.ustb.edu.cn/')->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $this->cookies->setCookie(new \GuzzleHttp\Cookie\SetCookie([
            'Domain' => 'n.ustb.edu.cn',
            'Name' => 'remember_token',
            'Value' => $token,
            'Discard' => false,
        ]));
    }

    private function checkToken()
    {
        $response = $this->client->request('GET', 'https://n.ustb.edu.cn/')->getBody()->getContents();

        if (strpos($response, '/logout') === false) {
            throw new InvalidArgumentException('Token expired');
        }
    }

    public function getClient()
    {
        return $this->client;
    }
}
