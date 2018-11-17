# Bitrix24

* sending leads to Bitrix24

## install 

```
composer require domatskiy/bitrix24
```

#usage


###create bitrix24 instance
```php
$connection = new Connection($domain, $port, $login, $password);
$Bitrix24 = new Bitrix24($connection);
$Bitrix24->debug(false, base_path());
```
  
###create lead
```php 
$lead = new Bitrix24\Lead('Request: ', Bitrix24\Lead::SOURCE_WEB, Bitrix24\Lead::STATUS_NEW, Bitrix24\Lead::CURRENCY_RUB);

# add fields to lead
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_NAME, $user_name);
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_PHONE_MOBILE, $phone);
$lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_EMAIL_HOME, $email);

# adding additional fields
$lead->addFieldExt('UF_XXXXXXX', '');

# adding file
$lead->addFile('UF_XXXXXXX', $absolute_path);
```

### sending lead
```php
$res = $Bitrix24->send($lead);
```
