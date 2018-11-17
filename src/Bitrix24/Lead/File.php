<?php

namespace Domatskiy\Bitrix24\Lead;

class File
{
    protected
        $path;

    /**
     * File constructor.
     * @param $path
     */
    function __construct($path)
    {
        if(!is_string($path) || mb_strlen($path) < 1)
            throw new Exception('not correct file path');
        elseif (!file_exists($path))
            throw new Exception('file "'.$path.'" not found');

        $this->path = $path;
    }

    /**
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

}
