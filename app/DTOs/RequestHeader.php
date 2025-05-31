<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequestHeader extends BaseDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $source,
        public string $usecase,
        public ?string $userId,
    )
    {
        //
    }

    public static function validations(Request $request): \Illuminate\Validation\Validator {
        return Validator::make($request->all(),[
            'request_header' => 'required',
            'request_header.source' => 'required',
            'request_header.usecase' => 'required',
        ]);
    }
}
