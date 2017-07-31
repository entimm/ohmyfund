<?php

namespace App\Exceptions;

/**
 * Class NonDataException.
 */
class NonDataException extends \Exception
{
    public function __construct($message = '')
    {
        parent::__construct($message ?: 'Non data');
    }
}
