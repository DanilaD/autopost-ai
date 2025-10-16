<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * URL Health Check Middleware
 *
 * This middleware performs comprehensive health checks on all application URLs
 * to ensure they don't return 500, 403, or 404 errors inappropriately.
 *
 * It logs any issues found and can optionally return detailed health reports.
 */
class UrlHealthCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only perform health checks in testing environment or when explicitly requested
        if (! $this->shouldPerformHealthCheck($request)) {
            return $response;
        }

        $this->performHealthCheck($request, $response);

        return $response;
    }

    /**
     * Determine if health check should be performed
     */
    private function shouldPerformHealthCheck(Request $request): bool
    {
        // Only in testing environment or when health check is explicitly requested
        return app()->environment('testing') ||
               $request->hasHeader('X-Health-Check') ||
               $request->query('health_check') === 'true';
    }

    /**
     * Perform comprehensive health check
     */
    private function performHealthCheck(Request $request, Response $response): void
    {
        $url = $request->fullUrl();
        $method = $request->method();
        $statusCode = $response->getStatusCode();

        // Check for problematic status codes
        $issues = $this->checkStatusCode($statusCode, $url, $method);

        // Check response content for error indicators
        $contentIssues = $this->checkResponseContent($response, $url);

        // Log any issues found
        if (! empty($issues) || ! empty($contentIssues)) {
            $this->logHealthIssues($url, $method, $statusCode, $issues, $contentIssues);
        }

        // Add health check headers to response
        $this->addHealthCheckHeaders($response, $issues, $contentIssues);
    }

    /**
     * Check if status code indicates a problem
     */
    private function checkStatusCode(int $statusCode, string $url, string $method): array
    {
        $issues = [];

        // Check for server errors (500)
        if ($statusCode === 500) {
            $issues[] = [
                'type' => 'server_error',
                'message' => 'URL returned 500 Internal Server Error',
                'severity' => 'critical',
            ];
        }

        // Check for forbidden errors (403) - might be legitimate for protected routes
        if ($statusCode === 403) {
            // Only flag as issue if it's not a protected route
            if (! $this->isExpectedProtectedRoute($url, $method)) {
                $issues[] = [
                    'type' => 'forbidden',
                    'message' => 'URL returned 403 Forbidden unexpectedly',
                    'severity' => 'warning',
                ];
            }
        }

        // Check for not found errors (404) - might be legitimate for non-existent resources
        if ($statusCode === 404) {
            // Only flag as issue if it's not an expected 404
            if (! $this->isExpectedNotFound($url, $method)) {
                $issues[] = [
                    'type' => 'not_found',
                    'message' => 'URL returned 404 Not Found unexpectedly',
                    'severity' => 'warning',
                ];
            }
        }

        return $issues;
    }

    /**
     * Check response content for error indicators
     */
    private function checkResponseContent(Response $response, string $url): array
    {
        $issues = [];
        $content = $response->getContent();

        // Check for error messages in content
        $errorPatterns = [
            'SQLSTATE' => 'Database error detected',
            'Fatal error' => 'Fatal PHP error detected',
            'Parse error' => 'PHP parse error detected',
            'Call to undefined' => 'Undefined function/method call',
            'Class \'[^\']*\' not found' => 'Class not found error',
            'Trying to get property' => 'Property access error',
            'Undefined variable' => 'Undefined variable error',
        ];

        foreach ($errorPatterns as $pattern => $message) {
            if (preg_match("/$pattern/i", $content)) {
                $issues[] = [
                    'type' => 'content_error',
                    'message' => $message,
                    'severity' => 'critical',
                ];
            }
        }

        // Check for empty responses on non-redirect routes
        if (empty(trim($content)) && ! $response->isRedirection()) {
            $issues[] = [
                'type' => 'empty_response',
                'message' => 'Response content is empty',
                'severity' => 'warning',
            ];
        }

        return $issues;
    }

    /**
     * Check if route is expected to be protected (403 is normal)
     */
    private function isExpectedProtectedRoute(string $url, string $method): bool
    {
        $protectedPatterns = [
            '/admin/',
            '/companies/settings',
            '/companies/invite',
            '/companies/users/',
            '/posts/create',
            '/posts/store',
            '/posts/\d+/edit',
            '/posts/\d+/update',
            '/posts/\d+/destroy',
            '/posts/\d+/schedule',
            '/profile',
            '/instagram/connect',
            '/instagram/\d+/disconnect',
            '/instagram/\d+/sync',
        ];

        foreach ($protectedPatterns as $pattern) {
            if (preg_match("#$pattern#", $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if 404 is expected for this route
     */
    private function isExpectedNotFound(string $url, string $method): bool
    {
        $expectedNotFoundPatterns = [
            '/posts/\d+$', // Individual post might not exist
            '/media/nonexistent',
            '/storage/nonexistent',
            '/reset-password/invalid-token',
            '/verify-email/invalid-id/invalid-hash',
            '/invitations/invalid-token',
        ];

        foreach ($expectedNotFoundPatterns as $pattern) {
            if (preg_match("#$pattern#", $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log health issues
     */
    private function logHealthIssues(string $url, string $method, int $statusCode, array $issues, array $contentIssues): void
    {
        $allIssues = array_merge($issues, $contentIssues);

        foreach ($allIssues as $issue) {
            $logLevel = $issue['severity'] === 'critical' ? 'error' : 'warning';

            Log::channel('single')->$logLevel('URL Health Check Issue', [
                'url' => $url,
                'method' => $method,
                'status_code' => $statusCode,
                'issue_type' => $issue['type'],
                'message' => $issue['message'],
                'severity' => $issue['severity'],
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Add health check headers to response
     */
    private function addHealthCheckHeaders(Response $response, array $issues, array $contentIssues): void
    {
        $allIssues = array_merge($issues, $contentIssues);
        $criticalIssues = array_filter($allIssues, fn ($issue) => $issue['severity'] === 'critical');
        $warningIssues = array_filter($allIssues, fn ($issue) => $issue['severity'] === 'warning');

        $response->headers->set('X-Health-Check-Status', empty($allIssues) ? 'healthy' : 'issues_found');
        $response->headers->set('X-Health-Check-Critical-Count', count($criticalIssues));
        $response->headers->set('X-Health-Check-Warning-Count', count($warningIssues));

        if (! empty($allIssues)) {
            $response->headers->set('X-Health-Check-Issues', json_encode($allIssues));
        }
    }
}
