<?php
namespace Domatskiy\Tests;

use Illuminate\Http\File;
use PHPUnit\Framework\TestCase;

class Bitrix24Test extends TestCase
{
    private $config;
    public function setUp()
    {
        $reader = new \Piwik\Ini\IniReader();
        $this->config = $reader->readFile(__DIR__.'/config.ini');

        if(!isset($this->config['bitrix24']))
            throw new \Exception('no config for bitrix24');

        if(!isset($this->config['fields']))
            throw new \Exception('no config for fields');

        if(!isset($this->config['bitrix24']['host']) && !$this->config['bitrix24']['host'])
            throw new \Exception('no config host for bitrix24');

        if(!isset($this->config['bitrix24']['port']) && !$this->config['bitrix24']['port'])
            throw new \Exception('no config port for bitrix24');

        if(!isset($this->config['bitrix24']['login']) && !$this->config['bitrix24']['login'])
            throw new \Exception('no config login for bitrix24');

        if(!isset($this->config['bitrix24']['password']) && !$this->config['bitrix24']['password'])
            throw new \Exception('no config password for bitrix24');

    }
    public function tearDown()
    {

    }

    public function testSubmit()
    {
        $config_bx = $this->config['bitrix24'];
        $bitrix24 = new \Domatskiy\Bitrix24($config_bx['host'], $config_bx['port'], $config_bx['login'], $config_bx['password']);
        $bitrix24->debug(true, 'log');

        $lead = new \Domatskiy\Bitrix24\Lead('TEST_SEND', \Domatskiy\Bitrix24\Lead::SOURCE_WEB, \Domatskiy\Bitrix24\Lead::STATUS_IN_PROCESS);

        $lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_NAME, 'user_name');
        $lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_PHONE_MOBILE, '+79111111111');
        $lead->addField(\Domatskiy\Bitrix24\Lead::FIELD_EMAIL_HOME, 'test@test.ru');

        // FILE
        $file_one = new File(__DIR__.'/one.txt', true);
        $lead->addFieldExt($this->config['fields']['file'], $file_one);

        // FILES
        $file_multi = new File(__DIR__.'/multi.txt');
        $lead->addFieldExt($this->config['fields']['files'], [$file_multi, $file_multi]);

        $rs = $bitrix24->send($lead);

        //$this->assertInstanceOf(Response::class, $response);
    }

}
