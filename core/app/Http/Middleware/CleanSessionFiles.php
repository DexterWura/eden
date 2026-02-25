<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanSessionFiles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ($request->hasSession()) {
            $session = $request->session();
            $reflection = new \ReflectionClass($session);
            $attributesProp = $reflection->getProperty('attributes');
            $attributesProp->setAccessible(true);
            $attributes = $attributesProp->getValue($session);
            $cleaned = $this->removeFilesFromArray($attributes);
            
            if ($cleaned !== $attributes) {
                $attributesProp->setValue($session, $cleaned);
            }
        }
    }

    /**
     * Recursively remove UploadedFile instances from array
     */
    private function removeFilesFromArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                // Skip UploadedFile instances
                continue;
            } elseif (is_array($value)) {
                // Recursively clean arrays
                $cleaned[$key] = $this->removeFilesFromArray($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}

