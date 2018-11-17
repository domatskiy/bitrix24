<?php

namespace Domatskiy\Bitrix24;

class Result
{
    protected
        $id,
        $message;

    function __construct($id, $message)
    {
        $this->id = $id;
        $this->message = $message;
    }

    function getID()
    {
        return $this->id;
    }

    function getMessage()
    {
        return $this->message;
    }
}
