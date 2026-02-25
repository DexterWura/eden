<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laramin\Utility\Onumoti;

abstract class Controller
{
    public function __construct()
    {
        $className = get_called_class();
        Onumoti::mySite($this,$className);
    }

    public static function middleware()
    {
        return [];
    }

    /**
     * Handle exceptions with proper logging and user-friendly responses
     */
    protected function handleException(\Throwable $e, Request $request, string $context = 'general')
    {
        // Log detailed error information
        Log::error("Exception in {$context}: " . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id() ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'input' => $request->except(['password', 'password_confirmation', '_token'])
        ]);

        // Return user-friendly error response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.',
                'error_type' => strtoupper($context) . '_ERROR'
            ], 500);
        }

        return back()->withNotify([['error', 'An unexpected error occurred. Please try again.']]);
    }

    /**
     * Safely execute database operations with proper error handling
     */
    protected function safeDatabaseOperation(callable $operation, Request $request, string $context = 'database')
    {
        try {
            return $operation();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Database error in {$context}: " . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id() ?? null
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A database error occurred. Please try again.',
                    'error_type' => 'DATABASE_ERROR'
                ], 500);
            }

            return back()->withNotify([['error', 'A database error occurred. Please try again.']]);
        } catch (\Exception $e) {
            return $this->handleException($e, $request, $context);
        }
    }

    /**
     * Validate marketplace-specific business rules
     */
    protected function validateMarketplaceRules(Request $request, array $rules): array
    {
        try {
            return $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation failures for monitoring
            Log::info('Validation failed', [
                'errors' => $e->errors(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id() ?? null,
                'input' => $request->except(['password', 'password_confirmation', '_token'])
            ]);

            throw $e; // Re-throw to let Laravel handle the response
        }
    }
}
