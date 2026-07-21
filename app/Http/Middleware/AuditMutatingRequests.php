<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMutatingRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user() || ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        if (! $routeName || str_starts_with($routeName, 'audit-logs.')) {
            return $response;
        }

        $segments = explode('.', $routeName);
        $module = str_replace('-', ' ', $segments[0] ?? 'system');
        $action = $segments[count($segments) - 1] ?? strtolower($request->method());
        $description = sprintf(
            '%s performed %s in %s.',
            $request->user()->full_name,
            str_replace('-', ' ', $action),
            $module
        );

        try {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'method' => $request->method(),
                'route_name' => $routeName,
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'status_code' => $response->getStatusCode(),
                    'route_parameters' => collect($request->route()?->parameters() ?? [])
                        ->map(fn ($value) => is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : $value)
                        ->all(),
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }
}
