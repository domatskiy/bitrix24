<?php
namespace Domatskiy\Tests;

use Domatskiy\Bitrix24;

class Bitrix24Test extends \PHPUnit_Framework_TestCase
{
    private $config;
    public function setUp()
    {
        $reader = new \Piwik\Ini\IniReader();
        $this->config = $reader->readFile(__DIR__.'/config.ini');

        if(!isset($this->config['bitrix24']))
            throw new \Exception('no config for bitrix24');

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
        $bitrix24 = new Bitrix24($config_bx['host'], $config_bx['port'], $config_bx['login'], $config_bx['password']);

        $lead = new Bitrix24\Lead('TEST_SEND');
        $rs = $bitrix24->send($lead);

        //$this->assertInstanceOf(Response::class, $response);
    }

}
