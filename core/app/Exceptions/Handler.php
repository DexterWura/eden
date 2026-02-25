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
     * Handle custom exceptions and provide user-friendly responses
     */
    protected function handleCustomExceptions(Throwable $e, Request $request)
    {
        // Handle marketplace-specific errors
        if ($e instanceof \Illuminate\Database\QueryException) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->view('errors.500', [
                    'message' => 'A data integrity error occurred. Please contact support.',
                    'error_code' => 'DATA_INTEGRITY_ERROR'
                ], 500);
            }
        }

        // Handle auction-specific errors
        if (str_contains($e->getMessage(), 'auction') || str_contains($e->getMessage(), 'bid')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred with the auction process. Please try again.',
                    'error_type' => 'AUCTION_ERROR'
                ], 500);
            }
        }

        // Handle escrow-specific errors
        if (str_contains($e->getMessage(), 'escrow')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred with the escrow process. Please contact support.',
                    'error_type' => 'ESCROW_ERROR'
                ], 500);
            }
        }

        // Handle rate limiting errors
        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please wait and try again.',
                    'error_type' => 'RATE_LIMITED'
                ], 429);
            }
        }

        // Handle validation errors with better formatting
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_type' => 'VALIDATION_ERROR'
                ], 422);
            }
        }

        // Handle 404 errors with custom marketplace message
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            if ($request->is('marketplace/*') || $request->is('user/*')) {
                return response()->view('errors.marketplace-404', [], 404);
            }
        }

        // Don't expose sensitive error details in production
        if (app()->environment('production')) {
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
                if ($statusCode >= 500) {
                    return response()->view('errors.500', [
                        'message' => 'An unexpected error occurred. Our team has been notified.',
                        'error_code' => 'INTERNAL_ERROR'
                    ], $statusCode);
                }
            }
        }

        return null; // Let Laravel handle it normally
    }

    /**
     * Convert an authentication exception into a redirect, setting a friendly intended URL when applicable.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        $intendedUrl = null;
        $routeName = $request->route()?->getName();
        $isPost = $request->isMethod('POST');

        if ($routeName === 'marketplace.nda.sign') {
            $listingId = $request->route('listingId');
            if ($listingId) {
                $intendedUrl = route('marketplace.nda.show', $listingId);
            }
        }

        if ($intendedUrl) {
            session()->put('url.intended', $intendedUrl);
        } elseif ($isPost) {
            session()->put('url.intended', route('user.home'));
        }

        $redirectTo = method_exists($exception, 'redirectTo') ? $exception->redirectTo($request) : null;
        return redirect()->guest($redirectTo ?? route('user.login'));
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
