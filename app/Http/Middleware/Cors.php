<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }

        // Handle actual request
        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Expose-Headers', 'Authorization');
    }

    /**
     * Get allowed origin based on request origin
     * 
     * @param Request $request
     * @return string
     */
    private function getAllowedOrigin(Request $request)
    {
        $origin = $request->headers->get('Origin');
        
        // List of allowed origins (configure this based on your frontend URLs)
        $allowedOrigins = [
            'http://localhost:3000',      // React default
            'http://localhost:4200',      // Angular default
            'http://localhost:8080',      // Vue default
            'http://localhost:5173',      // Vite default
            'http://127.0.0.1:3000',
            'http://127.0.0.1:4200',
            'http://127.0.0.1:8080',
            'http://127.0.0.1:5173',
        ];

        // Add production URLs from env
        if (config('app.frontend_url')) {
            $allowedOrigins[] = config('app.frontend_url');
        }

        // Check if origin is in allowed list
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // For development, allow all origins (REMOVE IN PRODUCTION)
        if (config('app.env') === 'local') {
            return $origin ?: '*';
        }

        // Default to first allowed origin or wildcard
        return $allowedOrigins[0] ?? '*';
    }
}