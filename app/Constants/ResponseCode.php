<?php

namespace App\Constants;

use App\Exceptions\ApiException;

class ResponseCode
{
    public static function internalServerError($errors = null): ApiException
    {
        return new ApiException(
            "internal server error",
            500,
            "500000",
            $errors
        );
    }

    public static function notFound($errors = null): ApiException
    {
        return new ApiException(
            "the resource not found",
            404,
            "404000",
            null,
            $errors,
        );
    }

    public static function fileIsNotExits(): ApiException
    {
        return new ApiException(
            'File not found.',
            404,
            '404001'
        );
    }
    public static function userNotFound($errors = null): ApiException
    {
        return new ApiException(
            "the user is not found",
            404,
            "404002",
            null,
            $errors,
        );
    }

    public static function badRequest($errors = null): ApiException
    {
        return new ApiException(
            "request is not valid",
            400,
            "400000",
            null,
            $errors,
        );
    }


    public static function passwordNotValid($errors = null): ApiException
    {
        return new ApiException(
            "Password must be 8–72 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.",
            400,
            "400001",
            null,
            $errors,
        );
    }

    public static function stockNotEnough(int $currentStock): ApiException
    {
        return new ApiException(
            "Insufficient stock: current stock is {$currentStock}",
            400,
            "400002"
        );
    }

    public static function unauthorized($errors = null): ApiException
    {
        return new ApiException(
            "UNAUTHORIZED",
            401,
            "401000",
            $errors
        );
    }

    public static function passwordOrEmailIsInvalid(): ApiException
    {
        return new ApiException(
            "invalid email or password",
            401,
            "401001",
            null
        );
    }

    public static function userIsNotActive(): ApiException
    {
        return new ApiException(
            "user is not active",
            401,
            "401002",
            null
        );
    }

    public static function tokenIsNotValid(): ApiException
    {
        return new ApiException(
            "token is not valid",
            401,
            "401003",
            null
        );
    }

    public static function currentPasswordInvalid(): ApiException
    {
        return new ApiException(
            "Current password is incorrect",
            401,
            "401004"
        );
    }

}
