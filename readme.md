# Bitrix24

sending leads to Bitrix24

## install 

```
composer require domatskiy/bitrix24
```

#usage


###create bitrix24 instance
```php
$Bitrix24 = new Bitrix24($domain, $port, $login, $password);
$Bitrix24->debug(false, base_path());
```
  
###create lead
```php 
$lead = new Bitrix24\Lead('Request: ', Bitrix24\Lead::SOURCE_WEB, Bitrix24\Lead::STATUS_NEW, Bitrix24\Lead::CURRENCY_RUB);

# add fields to lead
$lead->addField(Bitrix24\Lead::FIELD_NAME, $this->name);
$lead->addField(Bitrix24\Lead::FIELD_EMAIL_HOME, $this->email);
$lead->addField(Bitrix24\Lead::FIELD_PHONE_MOBILE, $this->phone);

# adding additional fields
$lead->addFieldExt('UF_XXXXXXX', '');
```

### sending lead
```php
$res = $Bitrix24->send($lead);
```
