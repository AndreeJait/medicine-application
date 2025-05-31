<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public string $codeError;
    public mixed $additionalData;

    public mixed $errors;

    public function __construct(string $message = "internal server error", int $code = 500, string $codeError = "500001", $additionalData = null, $errors = []) {
        parent::__construct($message, $code);
        $this->codeError = $codeError;
        $this->additionalData = $additionalData;
        $this->errors = $errors;
    }


    public function __toString(): string
    {
        return $this->codeError.": ".$this->getMessage();
    }

    public function wrapWithError($errors) {
        $this->errors = $errors;
    }
}
