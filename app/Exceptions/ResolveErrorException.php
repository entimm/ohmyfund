<?php

namespace App\Exceptions;

/**
 * Class ResolveRecordException
 *
 */
class ResolveErrorException extends \Exception
{
    private $data;

    public function __construct($message, $data)
    {
        parent::__construct($message);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
