<?php

namespace Domatskiy\Bitrix24;

use Domatskiy\Bitrix24;

class Lead
{
    const STATUS_NEW = 'NEW'; # новый
    const STATUS_ASSIGNED = 'ASSIGNED'; # Назначен ответственный
    const STATUS_DETAILS = 'DETAILS'; # Уточнение информации
    const STATUS_CANNOT_CONTACT = 'CANNOT_CONTACT'; # Не удалось связаться
    const STATUS_IN_PROCESS = 'IN_PROCESS'; # В обработке
    const STATUS_ON_HOLD = 'ON_HOLD'; # Обработка приостановлена
    const STATUS_RESTORED = 'RESTORED'; # Восстановлен
    const STATUS_CONVERTED = 'CONVERTED'; # Сконвертирован
    const STATUS_JUNK = 'JUNK'; # Некачественный лид

    const FIELD_TITLE = 'TITLE';
    const FIELD_NAME = 'NAME';
    const FIELD_EMAIL_HOME = 'EMAIL_HOME';
    const FIELD_PHONE_MOBILE = 'PHONE_MOBILE';

    const SOURCE_SELF = 'SELF'; # Свой контакт
    const SOURCE_PARTNER = 'PARTNER'; # Существующий клиент
    const SOURCE_CALL = 'CALL'; # Звонок
    const SOURCE_WEB = 'WEB'; # Веб-сайт


    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR = 'EUR';

    private $source,
            $status,
            $currency;

    protected $data = [];
    protected $data_files = [];

    function __construct($title, $source = null, $status = null, $currency = null)
    {

        if($status && !array_key_exists($status, self::getStatusList()))
            throw new \Exception('not correct status');

        $this->data['TITLE'] = $title;
        $this->source = $source;
        $this->status = $status;
        $this->currency = $currency;
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_ASSIGNED => 'Назначен ответственный',
            self::STATUS_DETAILS => 'Уточнение информации',
            self::STATUS_CANNOT_CONTACT => 'Не удалось связаться',
            self::STATUS_IN_PROCESS => 'В обработке',
            self::STATUS_ON_HOLD => 'Обработка приостановлена',
            self::STATUS_RESTORED => 'Восстановлен',
            self::STATUS_CONVERTED => 'Сконвертирован',
            self::STATUS_JUNK => 'Некачественный лид',
            ];
    }

    public static function getFieldsList()
    {
        return [
            self::FIELD_TITLE => '',
            self::FIELD_NAME => 'Имя',
            self::FIELD_EMAIL_HOME => '',
            self::FIELD_PHONE_MOBILE => ''
        ];
    }

    public function addField($code, $value)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        if(!array_key_exists($code, self::getFieldsList()))
            throw new \Exception('not correct field '.$code);

        $this->data[$code] = $value;
    }

    public function addFieldExt($code, $value)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        $this->data[$code] = $value;
    }

    /**
     * @param string $code
     * @param Bitrix24\Lead\File|Bitrix24\Lead\File[] $path
     * @throws \Exception
     */
    public function addFile(string $code, $file)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        if(is_array($file))
        {
            /**
             * @var $f Bitrix24\Lead\File
             */
            foreach ($file as $index => $f)
            {
                if(!($f instanceof Bitrix24\Lead\File))
                    throw new \Exception('not correct file '.$index);
            }

        }
        elseif(!($file instanceof Bitrix24\Lead\File))
            throw new \Exception('not correct file');

        $this->data_files[$code] = $file;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getFileFields()
    {
        return $this->data_files;
    }

}
