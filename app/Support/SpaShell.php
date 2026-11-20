<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SpaShell
{
    /**
     * Navigation and session counts shared by the public React pages.
     *
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        $services = [];

        foreach (config('backlink_services.menu', []) as $item) {
            $services[] = [
                'name' => $item['name'] ?? '',
                'line' => $item['line'] ?? '',
                'slug' => $item['slug'] ?? '',
                'href' => url('/services/'.($item['slug'] ?? '')),
            ];
        }

        $niches = [
            'Technology', 'Software & IT', 'Business', 'Finance', 'Health', 'Education',
            'Travel', 'Fashion & Beauty', 'Lifestyle', 'Real Estate', 'E-commerce',
            'Automotive', 'Sports', 'Food', 'Agriculture & Farming', 'Legal',
        ];

        $user = $request->user();
        $cartCount = 0;
        $cartSubtotal = 0.0;
        $cartItems = [];

        foreach ($request->session()->get('shopping_cart', []) as $key => $item) {
            if (! is_array($item) || blank($item['domain'] ?? null)) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = is_numeric($item['price'] ?? null) ? (float) $item['price'] : null;
            $prefix = trim((string) ($item['price_prefix'] ?? ''));
            $websiteCount = is_array($item['websites'] ?? null) ? count($item['websites']) : 0;
            $cartCount += $quantity;

            if ($price !== null) {
                $cartSubtotal += $price * $quantity;
            }

            $cartItems[] = [
                'line' => (string) ($item['line'] ?? $key),
                'domain' => (string) $item['domain'],
                'title' => (string) ($item['display_name'] ?? $item['domain']),
                'order_label' => (string) ($item['order_label'] ?? (($item['kind'] ?? '') === 'package' ? 'Backlink campaign' : 'Guest post')),
                'websites_label' => $websiteCount > 0 ? $websiteCount.' '.($websiteCount === 1 ? 'website' : 'websites') : '',
                'quantity' => $quantity,
                'money' => $price === null
                    ? 'Price on request'
                    : (($prefix !== '' ? $prefix.' ' : '').'$'.number_format($price * $quantity, 2)),
                'badge' => (string) ($item['backlinks'] ?? $item['article_length'] ?? ''),
            ];
        }

        $wishlistCount = 0;
        $wishlistDomains = [];

        foreach ($request->session()->get('wishlist', []) as $key => $item) {
            if (is_array($item) && filled($item['domain'] ?? null)) {
                $wishlistCount++;
                $wishlistDomains[] = mb_strtolower((string) $item['domain']);
            } elseif (is_string($key) && $key !== '') {
                $wishlistDomains[] = mb_strtolower($key);
            }
        }

        return [
            'services' => $services,
            'packages' => PublicPackages::groups(),
            'sites' => PublicSites::items(),
            'tools' => PublicTools::items(),
            'niches' => array_map(fn (string $label): array => [
                'label' => $label,
                'href' => route('niches.show', Str::slug($label)),
            ], $niches),
            'cartCount' => $cartCount,
            'cartSubtotal' => round($cartSubtotal, 2),
            'cartItems' => $cartItems,
            'countries' => BrowseCountries::all(),
            'wishlistCount' => $wishlistCount,
            'wishlistDomains' => array_values(array_unique($wishlistDomains)),
            'auth' => $user instanceof User ? [
                'name' => $user->name,
                'email' => $user->email,
                'dashboard' => $user->dashboardUrl(),
            ] : null,
            'loginHref' => Route::has('auth.login') ? route('auth.login') : url('/login'),
            'accountHref' => Route::has('customer.dashboard') ? route('customer.dashboard') : url('/login'),
            'logoutHref' => Route::has('auth.logout') ? route('auth.logout') : null,
        ];
    }
}
