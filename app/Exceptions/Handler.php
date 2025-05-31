<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Sanctum\Exceptions\MissingAbilityException;

class Handler extends ExceptionsHandler
{

    use ApiResponseTrait;

    //
    public function render($request, \Throwable $e): JsonResponse|\Symfony\Component\HttpFoundation\Response|Response
    {

        if(config('app.debug')) {
            logger()->error('Exception caught: ' . get_class($e));
        }
        if ($request->is('api/*')) {
            if ($e instanceof ApiException) {
                $errors = [];
                if($e->errors) {
                    if(is_array($e->errors)) {
                        $errors = $e->errors;
                    }else {
                        $errors = [$e->errors];
                    }
                }

                return $this->errorResponse(
                    $e->additionalData,
                    $e,
                    $e->codeError,
                    $e->getCode(),
                    $errors
                );
            }else if($e instanceof AuthenticationException or $e instanceof MissingAbilityException) {
                $traces = $e->getTrace();
                if(!config('app.debug')) {
                   $traces = [$e->getMessage()];
                }

                return $this->errorResponse(
                    null,
                    $e->getMessage(),
                    "401000",
                    401,
                    $traces,
                );
            }

            $traces = $e->getTrace();
            if(!config('app.debug')) {
               $traces = [$e->getMessage()];
            }
            return $this->errorResponse(
                null,
                $e->getMessage(),
                "500000",
                500,
                $e->getTrace(),
            );
        }
        return parent::render($request, $e);
    }
}
