<?php

namespace Domatskiy;

use App\Bitrix24\Exception\ArgumentException;
use App\Bitrix24\Exception\AuthException;
use App\Bitrix24\Lead;

class Bitrix24
{
    private $login = '',
            $password = '';

    private $port = '',
            $host = '';

    function __construct($host, $port, $login, $password)
    {
        $this->host = $host;
        $this->port = $port;

        $this->login = $login;
        $this->password = $password;
    }

    function send(Lead $lead, $callback = null)
    {
        $postData = [];

        #if (defined('AUTH'))
        #    $postData['AUTH'] = HASH;

        $postData['STATUS_ID'] = $lead->getStatus();
        $postData['SOURCE_ID'] = $lead->getSource();
        $postData['CURRENCY_ID'] = $lead->getCurrency();

        #$postData['PRODUCT_ID'] = 'PRODUCT_1';

        $postData['LOGIN'] = $this->login;
        $postData['PASSWORD'] = $this->password;
        #$postData['TITLE'] = '';

        foreach ($lead->getFields() as $key => $value)
            $postData[$key] = $value;

        $url = 'https://'.$this->host.'/crm/configs/import/lead.php';
        #$url = 'ssl://'.$this->host.':'.$this->port;

        $client = new \GuzzleHttp\Client([
            'timeout'  => 20.0,
            ]);

        $client->post($url, [
            'form_params' => $postData,
            'headers' => [
                'Host' => $this->host,
                'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

        echo $client->getStatusCode()."\n";

        if($client->getStatusCode() === 201) # Лид добавлен
        {
            echo 'ok '."\n";
            if($callback instanceof \Closure)
                call_user_func($callback);

            return true;
        }
        elseif ($client->getStatusCode() === 400) # Отсутствуют параметры или параметры не прошли проверку
        {
            throw new ArgumentException();
        }
        elseif ($client->getStatusCode() === 403) # Ошибка авторизации или доступа
        {
            throw new AuthException();
        }

        throw new \Exception();
    }
}