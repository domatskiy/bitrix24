<?php

namespace Domatskiy;

use Domatskiy\Bitrix24\Connection;
use Domatskiy\Bitrix24\Lead;
use Domatskiy\Bitrix24\Exception\ArgumentException;
use Domatskiy\Bitrix24\Exception\AuthException;

use Domatskiy\Bitrix24\Result;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Bitrix24
{
    /**
     * @var $connection Connection
     */
    private
        $connection;

    private
        $debug = false,
        $debug_log_path = '';

    /**
     * Bitrix24 constructor.
     * @param Connection $connection
     * @throws \Exception
     */
    function __construct($connection)
    {
        if(!($connection instanceof Connection))
            throw new \Exception('connection');

        $this->connection = $connection;
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
     * @return Result
     * @throws ArgumentException
     * @throws AuthException
     */
    function send(\Domatskiy\Bitrix24\Lead $lead): Result
    {
        return $this->sendLead($lead);
    }

    /**
     * @param Lead $lead
     * @return Result
     * @throws ArgumentException
     * @throws AuthException
     */
    function sendLead(\Domatskiy\Bitrix24\Lead $lead): Result
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

        $postData['LOGIN'] = $this->connection->getLogin();
        $postData['PASSWORD'] = $this->connection->getPassword();

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

        $url = 'https://'.trim($this->connection->getHost());

        if($this->connection->getPort())
            $url .= ':'.trim($this->connection->getPort());

        $url .= '/crm/configs/import/lead.php';

        $client = new \GuzzleHttp\Client([
            'timeout'  => 30.0,
            'headers' => [
                // 'Host' => $this->host,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
        ]);

        $config = [];

        if(!empty($multipartData))
        {
            foreach ($postData as $name => $co)
            {
                $multipartData[] = [
                    'name' => $name,
                    'contents' => $co
                ];
            }

            $config['multipart'] = $multipartData;
        }
        else
        {
            $config['form_params'] = $postData;
        }

        $response = $client->post($url, $config);

        $result = null;

        try{
            $contents = $response->getBody()->getContents();

            if($this->debug)
                $log->debug('contents='.$contents);

            $contents = str_replace('\'', '"', $contents);

            $result = @json_decode($contents);

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage(), $e->getCode());

        }

        #echo '$result=';
        #var_dump($result);

        /**
         * $result=object(stdClass)#694 (4) {
        ["error"]=>
        string(3) "201"
        ["ID"]=>
        string(2) "20"
        ["error_message"]=>
        string(23) "Лид добавлен"
        ["AUTH"]=>
        string(32) "fc1d45860c4a83026b721a8bc938d"
        }
         */

        if($response->getStatusCode() === 200) # Лид добавлен
        {
            if($this->debug)
                $log->debug('body '.print_r($response->getBody(), true));

            if($result && isset($result->error) && isset($result->error_message))
            {
                switch ((int)$result->error)
                {
                    case 200:
                    case 201:

                        $res = new Result($result->ID, $result->error_message);
                        return $res;

                        break;

                    case 400:

                        if($this->debug)
                            $log->warning($result->error_message);

                        throw new ArgumentException($result->error_message, $response->getStatusCode());

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

        throw new \Exception('Необработанный ответ');
    }
}
