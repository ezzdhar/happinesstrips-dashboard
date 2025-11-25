<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiExceptionHandlerException extends ExceptionHandler
{
    use ApiResponse;

    public function register(): void
    {
        if (request()->is('api/*')) {
            $this->renderable(function (NotFoundHttpException $e, $request) {
                return $this->responseNotFound();
            });

            $this->renderable(function (UnauthorizedHttpException $e, $request) {
                return $this->responseUnauthorized();
            });

            $this->renderable(function (AccessDeniedHttpException $e, $request) {
                return $this->responseForbidden();
            });

            $this->renderable(function (AuthenticationException $e, $request) {
                return $this->responseUnAuthenticated();
            });

            $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
                return $this->responseMethodNotAllowed();
            });

//            			$this->renderable(function (\Exception $e, $request) {
//            				return $this->responseInternalError();
//            			});
        }

    }
}
