<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait ApiResponse
{
    private const STATUS_SUCCESS = 200;

    private const STATUS_CREATED = 201;

    private const STATUS_BAD_REQUEST = 400;

    private const STATUS_UNAUTHORIZED = 401;

    private const STATUS_FORBIDDEN = 403;

    private const STATUS_NOT_FOUND = 404;

    private const STATUS_METHOD_NOT_ALLOWED = 405;

    private const STATUS_UNPROCESSABLE_ENTITY = 422;

    private const STATUS_INTERNAL_ERROR = 500;

    private function jsonResponse(bool $success, int $status, ?string $message = null, $data = null, $paginate = null): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data ?: null,
            'paginate' => $paginate ? [
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ] : null,
        ], $status);
    }

    public function responseOk($message = null, $data = null, $paginate = false): JsonResponse
    {
        return $this->jsonResponse(true, self::STATUS_SUCCESS, $message, $data, $paginate);
    }

    public function responseCreated($message = null, $data = null): JsonResponse
    {
        return $this->jsonResponse(true, self::STATUS_CREATED, $message, $data);
    }

    public function responseError($message = null, $data = null, $status = self::STATUS_BAD_REQUEST): JsonResponse
    {
        return $this->jsonResponse(false, $status, $message, $data);
    }

    public function responseUnauthorized(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_UNAUTHORIZED, __('lang.unauthorized'));
    }

    public function responseUnAuthenticated(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_UNAUTHORIZED, __('lang.unauthenticated'));
    }

    public function responseForbidden(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_FORBIDDEN, __('lang.forbidden'));
    }

    public function responseNotFound(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_NOT_FOUND, __('lang.not_found'));
    }

    public function responseInternalError(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_INTERNAL_ERROR, __('lang.server_error'));
    }

    public function responseMethodNotAllowed(): JsonResponse
    {
        return $this->jsonResponse(false, self::STATUS_METHOD_NOT_ALLOWED, __('lang.method_not_allowed'));
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->responseError($validator->errors()->first(), null, self::STATUS_UNPROCESSABLE_ENTITY));
    }
}
