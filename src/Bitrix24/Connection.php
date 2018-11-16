<?php

namespace Domatskiy\Bitrix24;


class Connection
{
    private $login = '',
            $password = '';

    private $port = '',
            $host = '';

    /**
     * Connection constructor.
     * @param $host string
     * @param $port string
     * @param $login string
     * @param $password string
     */
    function __construct(string $host, $port = null, string $login = '', string $password = '')
    {
        $this->host = $host;
        $this->port = $port;

        $this->login = $login;
        $this->password = $password;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
