<?php

namespace Domatskiy\Bitrix24\Lead;

class File implements \Serializable
{
    /**
     * @var string
     */
    protected $path;

    /**
     * File constructor.
     * @param string $path
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

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'path' => $this->path
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $d = unserialize($data);

        foreach (get_object_vars($this) as $code)
        {
            if(array_key_exists($code, $d))
                $this->{$code} = $d[$code];
        }
    }

}
