<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct(string $entityType, string $identifier)
    {
        parent::__construct("{$entityType} '{$identifier}' não encontrado");
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}
