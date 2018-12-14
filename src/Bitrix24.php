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
        $postData['method'] = 'lead.add';

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
        {
            if(is_array($value))
            {
                foreach ($value as $index => $val)
                    $postData[$key.'['.$index.']'] = $val;
            }
            else
                $postData[$key] = $value;
        }

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
                foreach ($value as $file)
                {
                    /**
                     * @var $file Lead\File
                     */

                    if(!($file instanceof Lead\File))
                        throw new \Exception('not correct file with index '.$index.' for field '.$key);

                    $multipartData[] = [
                        'name'     => $key.'[]',
                        'contents' => fopen($file->getPath(), 'r')
                    ];
                }
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

        $responseData = null;

        try{
            $contents = $response->getBody()->getContents();

            $contents = str_replace('\'', '"', $contents);
            $responseData = @json_decode($contents);

            if($this->debug)
                $log->debug('$responseData='.print_r($responseData, true));

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage(), $e->getCode());

        }

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
            if(!$responseData)
                throw new \Exception('not correct response');

            if(
                $responseData &&
                isset($responseData->error) &&
                isset($responseData->error_message)
            )
            {
                switch ((int)$responseData->error)
                {
                    case 200:
                    case 201:

                    if(!isset($responseData->ID))
                        throw new \Exception('not correct response, need ID');

                        $lead_add_result = new Result($responseData->ID, isset($responseData->error_message) ? $responseData->error_message : '');

                        if(isset($responseData->AUTH) && $responseData->AUTH)
                            $lead_add_result->setAuth($responseData->AUTH);

                        return $lead_add_result;

                        break;

                    case 400:

                        if($this->debug)
                            $log->warning($responseData->error_message);

                        throw new ArgumentException($responseData->error_message, $response->getStatusCode());

                        break;

                    case 403:
                        throw new AuthException($responseData->error_message);
                        break;

                    default:
                        throw new \Exception($responseData->error_message, $responseData->error);
                        break;
                }

            }

            throw new \Exception('not correct response');
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
