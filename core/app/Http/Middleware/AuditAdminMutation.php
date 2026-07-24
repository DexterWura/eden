<?php

namespace App\Http\Middleware;

use App\Models\AdminOperationNotification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $failed = $response->getStatusCode() >= 400
                || ($response->isRedirection() && $request->session()->has('errors'));
            admin_audit_log(
                'admin.request.' . str_replace('.', '_', (string) $request->route()?->getName()),
                $failed ? 'Admin operation failed.' : 'Admin operation completed.',
                null,
                [],
                [
                    'status_code' => $response->getStatusCode(),
                    'succeeded' => ! $failed,
                    'input_fields' => array_values(array_diff(array_keys($request->except([
                        'password', 'password_confirmation', 'current_password', 'code', '_token',
                    ])), ['_method'])),
                ]
            );

            if ($failed) {
                try {
                    AdminOperationNotification::create([
                        'admin_id' => auth('admin')->id(),
                        'type' => 'failed_operation',
                        'title' => 'Admin operation failed',
                        'message' => 'A guarded admin operation returned status ' . $response->getStatusCode() . '.',
                        'action_url' => null,
                    ]);
                } catch (\Throwable) {
                    // The operations migration may not have run yet; never replace the original response.
                }
            }
        }

        return $response;
    }
}
