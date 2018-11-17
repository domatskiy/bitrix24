<?php

namespace Domatskiy\Bitrix24;

use Domatskiy\Bitrix24;

class Lead implements \ArrayAccess, \Serializable
{
    /**
     * STATUS
     */
    const STATUS_NEW = 'NEW'; # новый
    const STATUS_ASSIGNED = 'ASSIGNED'; # Назначен ответственный
    const STATUS_DETAILS = 'DETAILS'; # Уточнение информации
    const STATUS_CANNOT_CONTACT = 'CANNOT_CONTACT'; # Не удалось связаться
    const STATUS_IN_PROCESS = 'IN_PROCESS'; # В обработке
    const STATUS_ON_HOLD = 'ON_HOLD'; # Обработка приостановлена
    const STATUS_RESTORED = 'RESTORED'; # Восстановлен
    const STATUS_CONVERTED = 'CONVERTED'; # Сконвертирован
    const STATUS_JUNK = 'JUNK'; # Некачественный лид

    /**
     * FIELDS
     */
    const FIELD_TITLE = 'TITLE';

    const FIELD_NAME = 'NAME';
    const FIELD_LAST_NAME = 'LAST_NAME';
    const FIELD_SECOND_NAME = 'SECOND_NAME';
    const FIELD_ADDRESS = 'ADDRESS';

    const FIELD_COMPANY_TITLE = 'COMPANY_TITLE';
    const FIELD_POST = 'POST';

    const FIELD_EMAIL_HOME = 'EMAIL_HOME';
    const FIELD_EMAIL_WORK = 'EMAIL_WORK';
    const FIELD_EMAIL_OTHER = 'EMAIL_OTHER';

    const FIELD_PHONE_MOBILE = 'PHONE_MOBILE';
    const FIELD_PHONE_WORK = 'PHONE_WORK';
    const FIELD_PHONE_FAX = 'PHONE_FAX';
    const FIELD_PHONE_HOME = 'PHONE_HOME';
    const FIELD_PHONE_PAGER = 'PHONE_PAGER';
    const FIELD_PHONE_OTHER = 'PHONE_OTHER';

    const FIELD_WEB_WORK = 'WEB_WORK';
    const FIELD_WEB_HOME = 'WEB_HOME';
    const FIELD_WEB_FACEBOOK = 'WEB_FACEBOOK';
    const FIELD_WEB_LIVEJOURNAL = 'WEB_LIVEJOURNAL';
    const FIELD_WEB_TWITTER = 'WEB_TWITTER';
    const FIELD_WEB_OTHER = 'WEB_OTHER';

    const FIELD_IM_SKYPE = 'IM_SKYPE';
    const FIELD_IM_ICQ = 'IM_ICQ';
    const FIELD_IM_MSN = 'IM_MSN';
    const FIELD_IM_JABBER = 'IM_JABBER';
    const FIELD_IM_OTHER = 'IM_OTHER';

    const FIELD_ASSIGNED_BY_ID = 'ASSIGNED_BY_ID';
    const FIELD_PRODUCT_ID = 'PRODUCT_ID';
    const FIELD_OPPORTUNITY = 'OPPORTUNITY';

    const FIELD_COMMENTS = 'COMMENTS';
    const FIELD_SOURCE_DESCRIPTION = 'SOURCE_DESCRIPTION';
    const FIELD_STATUS_DESCRIPTION = 'STATUS_DESCRIPTION';

    /**
     * SOURCE
     */
    const SOURCE_SELF = 'SELF'; # Свой контакт
    const SOURCE_PARTNER = 'PARTNER'; # Существующий клиент
    const SOURCE_CALL = 'CALL'; # Звонок
    const SOURCE_WEB = 'WEB'; # Веб-сайт
    const SOURCE_EMAIL = 'EMAIL';
    const SOURCE_CONFERENCE = 'CONFERENCE';
    const SOURCE_TRADE_SHOW = 'TRADE_SHOW';
    const SOURCE_EMPLOYEE = 'EMPLOYEE';
    const SOURCE_COMPANY = 'COMPANY';
    const SOURCE_HR = 'HR';
    const SOURCE_MAIL = 'MAIL';
    const SOURCE_OTHER = 'OTHER';

    /**
     * CURRENCY
     */
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR = 'EUR';

    private $source,
            $status,
            $currency;

    protected $data = [];
    protected $data_files = [];

    /**
     * Lead constructor.
     * @param $title
     * @param null $source
     * @param null $status
     * @param null $currency
     * @throws \Exception
     */
    function __construct($title, $source = null, $status = null, $currency = null)
    {

        if($status && !array_key_exists($status, self::getStatusList()))
            throw new \Exception('not correct status');

        $this->data['TITLE'] = $title;
        $this->source = $source;
        $this->status = $status;
        $this->currency = $currency;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public static function getFieldsList()
    {
        # TODO add fields

        return [
            self::FIELD_TITLE => '',

            self::FIELD_SOURCE_DESCRIPTION => 'source description',
            self::FIELD_STATUS_DESCRIPTION => 'status description',

            self::FIELD_NAME => 'name',
            self::FIELD_LAST_NAME => 'last name',
            self::FIELD_SECOND_NAME => 'second name',
            self::FIELD_ADDRESS => 'address',

            self::FIELD_EMAIL_HOME => 'home email',
            self::FIELD_EMAIL_WORK => 'work email',
            self::FIELD_EMAIL_OTHER => 'other email',

            self::FIELD_PHONE_MOBILE => 'mobile phone',
            self::FIELD_PHONE_HOME => 'home phone',
            self::FIELD_PHONE_WORK => 'work phone',
            self::FIELD_PHONE_PAGER => 'pager phone',
            self::FIELD_PHONE_FAX => 'fax',
            self::FIELD_PHONE_OTHER => 'other',

            self::FIELD_COMPANY_TITLE => 'company',
            self::FIELD_POST => 'post',

            self::FIELD_WEB_FACEBOOK => 'facebook',
            self::FIELD_WEB_HOME => 'personal site',
            self::FIELD_WEB_LIVEJOURNAL => 'livejornal',
            self::FIELD_WEB_TWITTER => 'twitter',
            self::FIELD_WEB_OTHER => 'other',
            self::FIELD_WEB_WORK => 'work site',

            self::FIELD_IM_ICQ => 'isq',
            self::FIELD_IM_JABBER => 'jabber',
            self::FIELD_IM_MSN => 'msn',
            self::FIELD_IM_SKYPE => 'skype',
            self::FIELD_IM_OTHER => 'other',

            self::FIELD_PRODUCT_ID => 'product id',
            self::FIELD_ASSIGNED_BY_ID => '',

            self::FIELD_COMMENTS => 'comments',
        ];
    }

    /**
     * @param $code
     * @param $value
     * @throws \Exception
     */
    public function addField($code, $value)
    {
        if(!is_string($code) || !$code)
            throw new \Exception('not correct code');

        if(!array_key_exists($code, self::getFieldsList()))
            throw new \Exception('not correct field '.$code);

        $this->data[$code] = $value;
    }

    /**
     * @param $code
     * @param $value
     * @throws \Exception
     */
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

    /**
     * @return null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return null
     */
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

    /**
     * @param string $code
     * @param mixed $value
     */
    public function __set($code, $value)
    {
        if(!is_string($code) || strlen($code) < 1)
            throw new \Exception('not correct file code');

        $this->data[$code] = $value;
    }

    public function __get($code)
    {
        if(array_key_exists($code, $this->data))
            return $this->data[$code];

        return null;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
            throw new \Exception('field code can not by null');

        $this->data[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
