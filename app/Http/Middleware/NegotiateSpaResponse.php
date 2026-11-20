<?php

namespace App\Http\Middleware;

use App\Http\StripeCheckout;
use App\Support\SpaShell;
use App\Support\SpaViewData;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NegotiateSpaResponse
{
    public function __construct(
        private SpaViewData $data,
        private SpaShell $shell,
    ) {}

    /**
     * Turn the existing controller view or redirect into JSON for React.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('X-Backlink-Spa')) {
            return $next($request);
        }

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        if ($response instanceof RedirectResponse) {
            return $this->withCookies($response, $this->redirect($request, $response));
        }

        $original = $response instanceof \Illuminate\Http\Response ? $response->getOriginalContent() : null;

        if ($original instanceof View) {
            return $this->withCookies($response, $this->page($request, $original));
        }

        if ($response->getStatusCode() >= 400) {
            return response()->json([
                'message' => $response->getStatusCode() === 404
                    ? 'That page could not be found.'
                    : 'The request could not be completed.',
            ], $response->getStatusCode());
        }

        return $response;
    }

    private function page(Request $request, View $view): JsonResponse
    {
        $data = $this->data->convert($view->getData());
        $payload = [
            'view' => $view->name(),
            'data' => $data,
            'shell' => $this->shell->forRequest($request),
            'flash' => $this->flash($request),
            'errors' => $this->errors($request),
            'old' => $request->session()->getOldInput(),
        ];

        if ($view->name() === 'customer.dashboard') {
            $payload['data']['linkServices'] = $this->linkServices();
        }

        if (in_array($view->name(), ['orders.payment', 'customer.wallet'], true)) {
            $payload['payments'] = [
                'demo' => StripeCheckout::usesDemo(),
                'stripe' => StripeCheckout::isConfigured(),
            ];
        }

        return response()->json($payload);
    }

    private function redirect(Request $request, RedirectResponse $response): JsonResponse
    {
        $target = $response->getTargetUrl();
        $parts = parse_url($target) ?: [];
        $sameHost = ($parts['host'] ?? $request->getHost()) === $request->getHost();
        $path = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');

        return response()->json([
            'redirect' => $sameHost ? $path : $target,
            'external' => ! $sameHost,
            'flash' => $this->flash($request),
            'errors' => $this->errors($request),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function flash(Request $request): array
    {
        $flash = [];

        foreach (['shopping_status', 'shopping_error', 'wishlist_status', 'wishlist_error', 'status', 'success', 'error', 'saved_coupon_popup', 'open_cart'] as $key) {
            $value = $request->session()->get($key);

            if ($value !== null && $value !== '') {
                $flash[$key] = $this->data->convert([$key => $value])[$key] ?? $value;
            }
        }

        return $flash;
    }

    /**
     * @return array<string, list<string>>
     */
    private function errors(Request $request): array
    {
        $bag = $request->session()->get('errors');

        if ($bag instanceof ViewErrorBag) {
            return $this->messageList($bag->getMessages());
        }

        if (! is_array($bag)) {
            return [];
        }

        $messages = [];

        foreach ($bag as $value) {
            if (! is_array($value) || ! is_array($value['messages'] ?? null)) {
                continue;
            }

            $messages = array_replace($messages, $this->messageList($value['messages']));
        }

        return $messages;
    }

    /**
     * @param  array<array-key, mixed>  $messages
     * @return array<string, list<string>>
     */
    private function messageList(array $messages): array
    {
        $list = [];

        foreach ($messages as $field => $fieldMessages) {
            $list[(string) $field] = array_values(array_map(
                strval(...),
                is_array($fieldMessages) ? $fieldMessages : [$fieldMessages],
            ));
        }

        return $list;
    }

    /**
     * @return list<array{slug: string, name: string, line: string, price: string}>
     */
    private function linkServices(): array
    {
        $prices = [];

        foreach (config('backlink_services.overview.compare_rows', []) as $row) {
            if (($row['label'] ?? '') === 'Price from') {
                $prices = $row;
            }
        }

        $menu = collect(config('backlink_services.menu', []))->keyBy('slug');
        $cards = [];

        foreach (['guest-post', 'niche-edit', 'contextual-link', 'text-link'] as $slug) {
            $item = $menu->get($slug, []);
            $cards[] = [
                'slug' => $slug,
                'name' => (string) ($item['name'] ?? $slug),
                'line' => (string) ($item['line'] ?? ''),
                'price' => (string) ($prices[$slug] ?? ''),
            ];
        }

        return $cards;
    }

    private function withCookies(Response $response, JsonResponse $json): JsonResponse
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $json->headers->setCookie($cookie);
        }

        return $json;
    }
}
