<?php

namespace Domatskiy\Bitrix24\Lead;

class File
{
    protected
        $path,
        $name;

    /**
     * File constructor.
     * @param $path
     * @param string $name
     * @throws Exception
     */
    function __construct($path, $name = '')
    {
        if(!is_string($path) || mb_strlen($path) < 1)
            throw new Exception('not correct file path');
        elseif (!file_exists($path))
            throw new Exception('file "'.$path.'" not found');

        if(!is_string($name))
            throw new Exception('not correct file name');

        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
}
