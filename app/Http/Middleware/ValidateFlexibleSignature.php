<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\Middleware\ValidateSignature;

class ValidateFlexibleSignature extends ValidateSignature
{
    /**
     * Accept both absolute and relative signed URLs.
     */
    public function handle($request, Closure $next, ...$args)
    {
        try {
            return parent::handle($request, $next, ...$args);
        } catch (InvalidSignatureException $exception) {
            if ($request->hasValidSignature(absolute: false)) {
                return $next($request);
            }

            throw $exception;
        }
    }
}
