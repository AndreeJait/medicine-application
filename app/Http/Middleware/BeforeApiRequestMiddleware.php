<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
use App\DTOs\RequestHeader;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BeforeApiRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->has('request_header')) {
            if (is_string($request->input('request_header'))) {
                $decoded = json_decode($request->input('request_header'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Merge back into request
                    $request->merge([
                        'request_header' => $decoded
                    ]);
                }
            }
        }

        if($request->method() === 'GET') {
            $requestHeader = [
                "source" => $request->get('source'),
                "usecase"=> $request->get('usecase'),
                "userId" => $request->get('userId')
            ];
            $request->merge([
                'request_header' => $requestHeader
            ]);
        }

        $ip = $request->header('HTTP_CLIENT_IP', $request->ip());

        $request->merge(['ip' => $ip]);

        if (config('app.debug')) {
            logger()->info("request: " . json_encode($request->toArray()));
        }

        $validator = RequestHeader::validations($request);
        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $response = $next($request);

        if (config('app.debug')) {
            logger()->info("response: " . $response->getContent());
        }

        return $response;
    }
}
