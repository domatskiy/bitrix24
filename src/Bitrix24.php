<?php

namespace Domatskiy;

use Domatskiy\Bitrix24\Lead;
use Domatskiy\Bitrix24\Exception\ArgumentException;
use Domatskiy\Bitrix24\Exception\AuthException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Bitrix24
{
    private $login = '',
            $password = '';

    private $port = '',
            $host = '';

    private $debug = false,
            $debug_log_path = '';

    function __construct($host, $port, $login, $password)
    {
        $this->host = $host;
        $this->port = $port;

        $this->login = $login;
        $this->password = $password;
    }

    public function debug($enable, $log_path)
    {
        $this->debug = $enable;
        $this->debug_log_path = $log_path;
    }

    function send(\Domatskiy\Bitrix24\Lead $lead)
    {
        if($this->debug)
        {
            $log = new Logger('bitrix24_lead_send');

            $date = date('Y-m-d');

            if($this->debug_log_path)
                $log->pushHandler(new StreamHandler($this->debug_log_path.'/bitrix24_lead_send'.$date.'.log', Logger::DEBUG));
        }

        $postData = [];
        $multipartData = [];

        $postData['LOGIN'] = $this->login;
        $postData['PASSWORD'] = $this->password;

        #if (defined('AUTH'))
        #    $postData['AUTH'] = HASH;

        if($lead->getStatus())
            $postData['STATUS_ID'] = $lead->getStatus();

        if($lead->getSource())
            $postData['SOURCE_ID'] = $lead->getSource();

        if($lead->getCurrency())
            $postData['CURRENCY_ID'] = $lead->getCurrency();

        #$postData['PRODUCT_ID'] = 'PRODUCT_1';

        foreach ($lead->getFields() as $key => $value)
            $postData[$key] = $value;

        foreach ($lead->getFileFields() as $key => $value)
        {
            if($value instanceof Lead\File)
            {
                /**
                 * @var $value Lead\File
                 */
                $multipartData[$key] = [
                    'name'     => $value->getName(),
                    'contents' => fopen($value->getPath(), 'r')
                ];
            }
            else
            {
                $tmp = [];
                foreach ($value as $file)
                {
                    /**
                     * @var $file Lead\File
                     */
                    $tmp[$key] = [
                        'name'     => $file->getName(),
                        'contents' => fopen($file->getPath(), 'r')
                    ];
                }

                $multipartData[$key] = $tmp;
            }
        }

        $url = 'https://'.trim($this->host);

        if($this->port)
            $url .= ':'.trim($this->port);

        $url .= '/crm/configs/import/lead.php';
        $log->info('url='.$url);

        $client = new \GuzzleHttp\Client([
            'timeout'  => 30.0,
            'headers' => [
                // 'Host' => $this->host,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
        ]);

        $config = [];

        //$config['body'] => json_encode($postData),

        if(!empty($postData))
            $config['form_params'] = $postData;

        if(!empty($multipartData))
            $config['multipart'] = $multipartData;

        /*'multipart' => [
                [
                    'name'     => 'file_name',
                    'contents' => fopen('/path/to/file', 'r')
                ],
                [
                    'name'     => 'csv_header',
                    'contents' => 'First Name, Last Name, Username',
                    'filename' => 'csv_header.csv'
                ]
            ]*/

        $response = $client->post($url, $config);

        if($response->getStatusCode() === 200) # Лид добавлен
        {
            if($this->debug)
                $log->debug('body '.print_r($response->getBody(), true));

            if($this->debug)
                $log->info('success');

            return true;
        }
        elseif ($response->getStatusCode() === 400) # Отсутствуют параметры или параметры не прошли проверку
        {
            $message = 'Отсутствуют параметры';

            if($this->debug)
                $log->warning($message);

            throw new ArgumentException($message, $response->getStatusCode());
        }
        elseif ($response->getStatusCode() === 403) # Ошибка авторизации или доступа
        {
            $message = 'Ошибка авторизации';

            if($this->debug)
                $log->warning($message);

            throw new AuthException($message, 403);
        }

        if($this->debug)
            $log->error('Responce status: '.$response->getStatusCode());

        throw new \Exception('Не обработанный ответ');
    }
}
