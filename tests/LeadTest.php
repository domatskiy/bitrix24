<?php

namespace Domatskiy\Tests;

#require '../vendor/autoload.php';

use Domatskiy\Bitrix24\Lead;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testCreateLead()
    {
        $lead = new \Domatskiy\Bitrix24\Lead('TEST');
        $this->assertTrue(($lead instanceof Lead), 'error messages not array');

        $lead->addField('TITLE', 'sd');
    }

    public function testAddField()
    {

    }

    public function testAddFieldExt()
    {

    }

}
