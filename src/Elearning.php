<?php

namespace Kotoyuuko\UstbElearning;

use Kotoyuuko\UstbElearning\Client\Client;
use Kotoyuuko\UstbElearning\Exceptions\InvalidArgumentException;
use PHPHtmlParser\Dom;

class Elearning
{
    protected $options;
    protected $client;

    public function __construct(Client $client, array $options = [])
    {
        $this->client = $client->getClient();
        $this->defaultOptions($options);
        $this->login();
    }

    public function __destruct()
    {
        $this->logout();
    }

    private function defaultOptions(array $options)
    {
        $defaults = [
            'uid' => null,
            'pwd' => null,
        ];

        $this->options = $options + $defaults;

        if ($this->options['uid'] === null || $this->options['pwd'] === null) {
            throw new InvalidArgumentException('Missing username or password for elearning system');
        }
    }

    public function login()
    {
        $response = $this->client->request('POST', 'j_spring_security_check', [
            'form_params' => [
                'j_username' => $this->options['uid'] . ',undergraduate',
                'j_password' => $this->options['pwd'],
            ],
        ])->getBody()->getContents();

        if (strpos($response, 'success:true') === false) {
            throw new InvalidArgumentException('Invalid username or password for elearning system');
        }

        echo 'logined' . "\n";
    }

    public function logout()
    {
        $this->client->request('GET', 'j_spring_security_logout');

        echo 'logouted' . "\n";
    }

    public function currentSemester()
    {
        $response = $this->client->request('GET', 'userStatus.action')->getBody()->getContents();

        $matches = [];
        preg_match('/gv\[\'g_Xnxq\'\]=\'(\d{4}\-\d{4}\-\d{1})\'/', $response, $matches);
        $semester = $matches['1'];

        $matches = [];
        preg_match('/Ext\.Date\.parse\(\'(\d{4}\-\d{2}\-\d{2})\', \'Y\-m\-d\'\)\.getTime\(\)/', $response, $matches);
        $startAt = $matches['1'];

        return [
            'semester' => $semester,
            'startAt' => $startAt,
        ];
    }

    public function createScore()
    {
        $response = $this->client->request('POST', 'information/singleStuInfo_singleStuInfo_loadSingleStuCxxfPage.action', [
            'form_params' => [
                'uid' => $this->options['uid'],
            ],
        ])->getBody()->getContents();

        $dom = new Dom;
        $dom->load($response);
        $data = [];
        foreach ($dom->find('table tbody tr') as $line) {
            $tdData = $line->find('td');
            $data[] = [
                'date' => $tdData['0']->text,
                'type' => $tdData['1']->text,
                'name' => $tdData['2']->text,
                'score' => $tdData['3']->text,
            ];
        }

        return $data;
    }

    public function courseScore()
    {
        $response = $this->client->request('POST', 'information/singleStuInfo_singleStuInfo_loadSingleStuScorePage.action', [
            'form_params' => [
                'uid' => $this->options['uid'],
            ],
        ])->getBody()->getContents();

        $dom = new Dom;
        $dom->load($response);
        $data = [];
        foreach ($dom->find('table tbody tr') as $line) {
            $tdData = $line->find('td');
            $data[] = [
                'semester' => $tdData['0']->text,
                'id' => $tdData['1']->text,
                'name' => $tdData['2']->text,
                'type' => $tdData['3']->text,
                'hour' => $tdData['4']->text,
                'credit' => $tdData['5']->text,
                'firstScore' => $tdData['6']->text,
                'score' => $tdData['7']->text,
            ];
        }

        return $data;
    }
}
