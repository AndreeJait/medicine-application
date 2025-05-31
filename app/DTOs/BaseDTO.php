<?php

namespace App\DTOs;

use Illuminate\Support\Str;
use JsonSerializable;

abstract class BaseDTO implements JsonSerializable
{
    public function toArray(): array
    {
        $vars = get_object_vars($this);

        $snakeCaseArray = [];
        foreach ($vars as $key => $value) {
            $snakeCaseArray[Str::snake($key)] = $value;
        }

        return $snakeCaseArray;
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
