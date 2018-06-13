# Bitrix24

поддерживаются:

* отправка лидов


## install 

```
composer require domatskiy/bitrix24
```

## use

```php
$bitrix24 = new \Domatskiy\Bitrix24(host, port, login, password);
 
// create lead
$lead = new \Domatskiy\Bitrix24\Lead('TEST', \Domatskiy\Bitrix24\Lead::SOURCE_WEB);
 
// add fields
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_NAME, 'user_name');
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_PHONE_MOBILE, '+79111111111');
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_EMAIL_HOME, 'test@test.ru');
 
$rs = $bitrix24->send($lead);
```
