<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'images',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logDetailedError($e);
        });

        $this->renderable(function (Throwable $e, $request) {
            return $this->handleCustomExceptions($e, $request);
        });
    }

    /**
     * Log detailed error information
     */
    protected function logDetailedError(Throwable $e): void
    {
        // Keep context helpful, but avoid bloating logs for expected/low-severity exceptions.
        $context = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'user_id' => auth()->id() ?? null,
            'session_id' => session()->getId(),
        ];

        // Log based on severity
        if ($e instanceof \Illuminate\Database\QueryException) {
            $context['trace'] = $e->getTraceAsString();
            Log::error('Database Error: ' . $e->getMessage(), $context);
            $this->notifySuperAdminsForSeriousError($e, $context);
        } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
            // No stack trace for validation errors; log the field errors instead.
            $context['errors'] = $e->errors();
            Log::warning('Validation Error: ' . $e->getMessage(), $context);
        } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
            Log::info('Authentication Error: ' . $e->getMessage(), $context);
        } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            Log::warning('Authorization Error: ' . $e->getMessage(), $context);
        } elseif ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            Log::warning('Throttle: ' . $e->getMessage(), $context);
        } elseif ($e instanceof HttpException) {
            if ($e->getStatusCode() >= 500) {
                $context['trace'] = $e->getTraceAsString();
                Log::error('HTTP Error ' . $e->getStatusCode() . ': ' . $e->getMessage(), $context);
                $this->notifySuperAdminsForSeriousError($e, $context);
            } else {
                Log::info('HTTP Error ' . $e->getStatusCode() . ': ' . $e->getMessage(), $context);
            }
        } else {
            $context['trace'] = $e->getTraceAsString();
            Log::error('Unexpected Error: ' . $e->getMessage(), $context);
            $this->notifySuperAdminsForSeriousError($e, $context);
        }
    }

    /**
     * Notify super admins of serious errors (throttled). Only when we have an HTTP request.
     */
    protected function notifySuperAdminsForSeriousError(Throwable $e, array $context): void
    {
        if (!app()->runningInConsole() && function_exists('notifySuperAdminsForError')) {
            try {
                notifySuperAdminsForError($e, [
                    'url' => $context['url'] ?? null,
                    'method' => $context['method'] ?? null,
                    'user_id' => $context['user_id'] ?? null,
                ]);
            } catch (\Throwable $notificationException) {
                Log::warning('Super admin error notification failed: ' . $notificationException->getMessage());
            }
        }
    }

    /**
     * Handle custom exceptions. When APP_DEBUG is true, show the real error (return null).
     * When APP_DEBUG is false, show custom error pages to the user.
     */
    protected function handleCustomExceptions(Throwable $e, Request $request)
    {
        $debug = config('app.debug', false);

        if ($request->expectsJson()) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_type' => 'VALIDATION_ERROR'
                ], 422);
            }
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please wait and try again.',
                    'error_type' => 'RATE_LIMITED'
                ], 429);
            }
            if (!$debug && $e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred.',
                    'error_type' => 'HTTP_ERROR'
                ], $e->getStatusCode());
            }
            return null;
        }

        if ($debug) {
            return null;
        }

        if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Illuminate\Auth\AuthenticationException) {
            return null;
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->view('errors.404', ['title' => 'Page not found'], 404);
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $statusCode = $e->getStatusCode();
            if ($statusCode === 403) {
                return response()->view('errors.403', ['title' => 'Access denied'], 403);
            }
            if ($statusCode === 419) {
                return response()->view('errors.419', ['title' => 'Page expired'], 419);
            }
            if ($statusCode === 429) {
                return response()->view('errors.429', ['title' => 'Too many requests'], 429);
            }
            if ($statusCode === 503) {
                return response()->view('errors.503', ['title' => 'Unavailable'], 503);
            }
            if ($statusCode >= 500) {
                return response()->view('errors.500', [
                    'title' => 'Server error',
                    'message' => 'An unexpected error occurred. We\'ve been notified and are working on it.',
                ], $statusCode);
            }
        }

        if ($e instanceof \Illuminate\Database\QueryException) {
            return response()->view('errors.500', [
                'title' => 'Server error',
                'message' => 'A temporary error occurred. Please try again or contact support.',
            ], 500);
        }

        return response()->view('errors.500', [
            'title' => 'Server error',
            'message' => 'An unexpected error occurred. We\'ve been notified.',
        ], 500);
    }

    /**
     * Convert an authentication exception into a redirect, setting a friendly intended URL when applicable.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        $redirectTo = method_exists($exception, 'redirectTo') ? $exception->redirectTo($request) : null;
        if ($redirectTo) {
            return redirect()->guest($redirectTo);
        }
        if ($request->is('admin*') || $request->is('backoffice*')) {
            return redirect()->guest(url('/admin'));
        }
        return redirect()->guest(url('/'));
    }

    /**
     * Convert a validation exception into a response.
     * Override to ensure files are never flashed to session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function invalid($request, \Illuminate\Validation\ValidationException $exception)
    {
        // Use input() which excludes files by default, then filter out any UploadedFile instances that might slip through
        $input = $request->input();
        
        // Also remove any UploadedFile instances that might be in the input (safety check)
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = array_filter($value, function($item) {
                    return !($item instanceof \Illuminate\Http\UploadedFile);
                });
            } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                unset($input[$key]);
            }
        }

        return redirect($exception->redirectTo ?? url()->previous())
                    ->withInput($input)
                    ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
    }
}
