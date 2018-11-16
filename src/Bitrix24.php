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

    /**
     * Bitrix24 constructor.
     * @param $host string
     * @param $port string
     * @param $login string
     * @param $password string
     */
    function __construct($host, $port, $login, $password)
    {
        $this->host = $host;
        $this->port = $port;

        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @param $enable boolean
     * @param $log_path string
     */
    public function debug($enable, $log_path)
    {
        $this->debug = $enable;
        $this->debug_log_path = $log_path;
    }

    /**
     * @param Lead $lead
     * @return bool
     * @throws ArgumentException
     * @throws AuthException
     */
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
                $multipartData[] = [
                    'name'     => $key, # $value->getName(),
                    'contents' => fopen($value->getPath(), 'r')
                ];
            }
            elseif(is_array($value))
            {
                $tmp = [];
                foreach ($value as $file)
                {
                    /**
                     * @var $file Lead\File
                     */
                    $multipartData[] = [
                        'name'     => $key.'[]', #$file->getName(),
                        'contents' => fopen($file->getPath(), 'r')
                    ];
                }

                // $multipartData[$key] = $tmp;
            }
        }

        $url = 'https://'.trim($this->host);

        if($this->port)
            $url .= ':'.trim($this->port);

        $url .= '/crm/configs/import/lead.php';

        $client = new \GuzzleHttp\Client([
            'timeout'  => 30.0,
            'headers' => [
                // 'Host' => $this->host,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
        ]);

        $config = [];

        //$config['body'] => json_encode($postData),

        // if(!empty($postData))
        //    $config['form_params'] = $postData;

        foreach ($postData as $name => $co)
        {
            $multipartData[] = [
                'name' => $name,
                'contents' => $co
            ];
        }


        if(!empty($multipartData))
            $config['multipart'] = $multipartData;

        var_dump($config);

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

            $result = null;

            try{
                $contents = $response->getBody()->getContents();

                if($this->debug)
                    $log->debug('contents='.$contents);

                $contents = str_replace('\'', '"', $contents);

                $result = @json_decode($contents);

            } catch (\Exception $e) {
                $result = null;
                throw new \Exception($e->getMessage(), $e->getCode());

                if($this->debug)
                    $log->error(''.$e->getMessage());

                return false;
            }

            if($result && isset($result->error) && isset($result->error_message))
            {
                switch ((int)$result->error)
                {
                    case 200:
                    case 201:
                        return $result->ID;
                        break;

                    case 403:
                        throw new AuthException($result->error_message);
                        break;

                    default:
                        throw new \Exception($result->error_message, $result->error);
                        break;
                }

            }

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
            $log->error('Response status: '.$response->getStatusCode());

        throw new \Exception('Не обработанный ответ');
    }
}
