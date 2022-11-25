<?php

namespace App\Exception;

use InvalidArgumentException;

class NullArgumentException extends InvalidArgumentException
{
    public function __construct(string $parameterName)
    {
        $functionName = $this->getTrace()[0]->function ?? $this->getFile();
        $message = "O parâmetro '$parameterName' não pode ser nulo em '$functionName'!";
        parent::__construct();
    }
}