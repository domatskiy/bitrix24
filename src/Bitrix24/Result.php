<?php

namespace Domatskiy\Bitrix24;

class Result
{
    protected
        $id,
        $message,
        $auth;

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

    function setAuth(string $auth): void
    {
        $this->auth = $auth;
    }

    function getAuth(): string
    {
        return $this->auth;
    }
}
