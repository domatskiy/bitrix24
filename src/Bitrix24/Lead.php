<?php

namespace Domatskiy\Bitrix24;

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

    public $fields = ['NAME', 'EMAIL_HOME', 'PHONE_MOBILE'];

    protected $data = [];

    function __construct($title, $source, $status, $currency)
    {

        if(!array_key_exists($status, self::getStatusList()))
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
            ];
    }

    public function addField($code, $value)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        if(!is_array($code, $this->fields))
            throw new \Exception('not correct field '.$code);

        $this->data[$code] = $value;
    }

    public function addFieldExt($code, $value)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        $this->data[$code] = $value;
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

    public function getFields()
    {
        return $this->data;
    }

}