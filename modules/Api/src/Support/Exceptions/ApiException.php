<?php

namespace Modules\Api\Support\Exceptions;

use Exception;

class ApiException extends Exception
{
    /** @var array<string, mixed> */
    public array $errors;

    public int $status;

    /**
     * @param string $message
     * @param array<string, mixed> $errors
     * @param int $status
     * @param int $code
     */
    public function __construct(string $message = '', array $errors = [], int $status = 400, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
        $this->status = $status;
    }
}
