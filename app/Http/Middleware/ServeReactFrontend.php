<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeReactFrontend
{
    /**
     * Serve the React document for the public site, checkout, and customer account.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldServe($request)) {
            return $next($request);
        }

        $index = public_path('app/index.html');

        if (! is_file($index)) {
            return $next($request);
        }

        return response((string) file_get_contents($index), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function shouldServe(Request $request): bool
    {
        if (! config('frontend.serve') || $request->headers->has('X-Backlink-Spa')) {
            return false;
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        $path = trim($request->path(), '/');

        if (preg_match('/\.(css|js|map|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|json|txt|xml|webmanifest)$/i', $path) === 1) {
            return false;
        }

        if ($path === '' || $path === 'home' || $path === 'contact' || $path === 'login' || $path === 'register') {
            return true;
        }

        if (preg_match('#/download$#', $path) === 1) {
            return false;
        }

        return (bool) preg_match(
            '#^(services|packages|publishers|niches|sites|tools|wishlist|cart|marketplace|publish|customer|orders)(/|$)#',
            $path,
        );
    }
}
