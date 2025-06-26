<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * BusinessException constructor.
     *
     * @param string $message
     * @param int|null $code
     */
    public function __construct(string $message, ?int $code = null)
    {
        parent::__construct($message, $code ?? $this->code);
    }
} 