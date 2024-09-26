<?php

namespace Winata\Core\Response\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Winata\Core\Response\Contracts\OnResponse;
use Winata\Core\Response\Enums\DefaultResponseCode;
use Winata\Core\Response\Exception\BaseException;
use Winata\Core\Response\Exception\HandleUnexpectedJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\UniqueConstraintViolationException;

trait CatchableError
{

    public ?OnResponse $rc = DefaultResponseCode::ERR_UNKNOWN;
    public ?string $message = null;
    public array|null $data = null;

    public Throwable $e;

    public function render($request, \Throwable $e): HandleUnexpectedJson|Response|JsonResponse|SymfonyResponse
    {
        if ($request->expectsJson()) {
            $e = $this->mapToBaseException($request, $e);

            $this->e = $e;
            if (isset($e->rc)) {
                $this->rc = $e->rc;
            }

            if (isset($e->data)) {
                $this->data = $e->data;
            }

            $this->message = $e->getMessage();
            return response()->json($this->toArray(), $this->rc->httpCode());
        }

        return parent::render($request, $e);
    }

    private function mapToBaseException(Request $request, \Throwable $e): BaseException|\Throwable
    {
        if ($e instanceof ModelNotFoundException) {
            return new BaseException(DefaultResponseCode::ERR_ENTITY_NOT_FOUND, DefaultResponseCode::ERR_ENTITY_NOT_FOUND->message(), previous: $e);
        }

        if ($e instanceof ValidationException) {
            return new BaseException(DefaultResponseCode::ERR_VALIDATION, $e->getMessage(), $e->errors(), previous: $e);
        }

//        if ($e instanceof OAuthServerException || $e instanceof AuthenticationException) {
//            return new BaseException(DefaultResponseCode::ERR_AUTHENTICATION, $e->getMessage(), null, $e);
//        }

        if ($e instanceof AuthenticationException) {
            return new BaseException(DefaultResponseCode::ERR_AUTHENTICATION, $e->getMessage(), null, $e);
        }

        if ($e instanceof NotFoundHttpException) {
            return new BaseException(DefaultResponseCode::ERR_ROUTE_NOT_FOUND, $e->getMessage(), null, $e);
        }

        if ($e instanceof UniqueConstraintViolationException) {
            return new BaseException(DefaultResponseCode::ERR_UNIQUE_RECORD, 'Unique Records Violation in Table', null, $e);
        }

        if ($e instanceof AuthorizationException || $e instanceof UnauthorizedException) {
            return new BaseException(DefaultResponseCode::ERR_ACTION_UNAUTHORIZED, $e->getMessage(), null, $e);
        }

        if($e instanceof QueryException) {
            $message = $e->getMessage();
            if (str($message)->contains('Foreign key violation') ){
                return new BaseException(DefaultResponseCode::ERR_RECORD_CONSTRAINT, __('Record Probably in use') , null, $e);
            }
            return new BaseException(DefaultResponseCode::ERR_RECORD_CONSTRAINT, null , null, $e);
        }
//
//        if (config('app.debug')) {
//            return $e;
//        }

        return new BaseException(
            rc: DefaultResponseCode::ERR_UNKNOWN,
            message: $e->getMessage(),
            data: [
                'base_url' => $request->getBaseUrl(),
                'path' => $request->getUri(),
                'origin' => $request->ip(),
                'method' => $request->getMethod(),
            ],
            previous: $e
        );
    }


    /**
     * Get response code.
     *
     * @return string|null
     */
    public function getResponseCode(): string|null
    {
        return $this->rc?->name ?? null;
    }

    /**
     * Get response message.
     *
     * @return string
     */
    public function getResponseMessage(): string
    {
        if (config('app.debug') && $this->e->getPrevious() instanceof Throwable) {
            return $this->e->getPrevious()->getMessage();
        }

        return $this->message;
    }

    /**
     * Get error data.
     *
     * @return array|null
     */
    public function getErrorData(): ?array
    {
        return $this->data;
    }

    /** {@inheritDoc} */
    public function toArray(): array
    {
        $carrier = [
            'rc' => $this->getResponseCode(),
            'message' => $this->getResponseMessage(),
            'timestamp' => now(),
            'payload' => $this->getErrorData(),
            'file' => $this->e->getFile(),
            'line' => $this->e->getLine(),
        ];

        if (config('app.debug') && $this->e->getPrevious() instanceof Throwable) {
            $carrier['debug'] = [
                'class' => get_class($this->e->getPrevious()),
                'file' => $this->e->getPrevious()->getFile(),
                'line' => $this->e->getPrevious()->getLine(),
                'trace' => $this->e->getPrevious()->getTrace(),
            ];
        }

        return $carrier;
    }
}
