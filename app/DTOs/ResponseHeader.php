<?php

namespace App\DTOs;

class ResponseHeader extends BaseDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $code = "200001",
        public bool $success = true,
        public string $message = "success",
        public $error = [],
    )
    {}
}
