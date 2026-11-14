<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Services\Customer\NicheListingService;
use App\Services\Customer\PublishOfferService;
use App\Support\PublicPackages;
use App\Support\PublicSites;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShoppingController extends Controller
{
    public function index(NicheListingService $listings): View
    {
        set_time_limit(120);

        $catalog = $this->catalog($listings);
        $items = $this->items($catalog);
        $priced = $items->filter(fn (array $item) => is_numeric($item['price'] ?? null));

        $held = [];

        foreach ($items as $item) {
            $held[mb_strtolower((string) $item['domain'])] = true;
        }

        $subtotal = (float) $priced->sum(fn (array $item) => (float) $item['price'] * (int) $item['quantity']);
        $discount = $this->discountFor($subtotal);

        return view('customer.shopping.index', [
            'items' => $items,
            'picks' => $catalog
                ->reject(fn (array $row) => isset($held[mb_strtolower((string) ($row['domain'] ?? ''))]))
                ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
                ->take(8)
                ->values(),
            'sites' => PublicSites::items(),
            'quantity' => (int) $items->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount),
            'coupon' => session('shopping_coupon'),
            'unpriced' => $items->count() - $priced->count(),
            'checkoutHref' => route('shopping.checkout'),
        ]);
    }

    public function store(Request $request, NicheListingService $listings, PublishOfferService $offers): RedirectResponse
    {
        set_time_limit(120);

        $data = $request->validate([
            'domain' => ['required_without:package_slug', 'nullable', 'string', 'max:255'],
            'site' => ['nullable', 'string', 'max:255'],
            'package_slug' => ['nullable', 'string', 'max:80'],
            'package_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'order_type' => ['nullable', 'string', 'max:60'],
            'content_topic' => ['nullable', 'string', 'max:80'],
            'project' => ['nullable', 'string', 'max:80'],
        ]);

        if (filled($data['package_slug'] ?? null)) {
            return $this->storePackage($data);
        }

        $offer = filled($data['site'] ?? null) ? $offers->resolve((string) $data['site']) : null;
        $row = $offer === null ? $this->find($listings, $data['domain']) : null;

        if ($offer === null && $row === null) {
            return redirect()->route('shopping.index')->with('shopping_error', 'That publisher is not on the list.');
        }

        $type = $this->orderType($data['order_type'] ?? null);
        $topic = $this->topic($data['content_topic'] ?? null);
        $project = trim((string) ($data['project'] ?? '')) ?: 'Default';
        $details = $offer !== null ? $this->fromOffer($offer) : $this->fromRow($row);

        $line = $this->lineKey($details['domain'], $type['key'], $topic);
        $items = $this->sessionItems();
        $current = (int) ($items[$line]['quantity'] ?? 0);
        $quantity = min(20, $current + (int) ($data['quantity'] ?? 1));

        $items[$line] = $this->build($line, $details, $type, $topic, $project, $quantity);
        session(['shopping_cart' => $items]);

        return back()
            ->with('open_cart', true)
            ->with('shopping_status', 'Publish Guest Post on '.$details['display_name'].' was added to your cart.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storePackage(array $data): RedirectResponse
    {
        $slug = (string) $data['package_slug'];
        $item = PublicPackages::find($slug);
        $story = PublicPackages::story($slug);

        if ($item === null || $story === null) {
            return redirect()->route('packages.index')->with('shopping_error', 'That package is not available.');
        }

        $price = isset($item['amount']) && is_numeric($item['amount']) ? (float) $item['amount'] : null;
        $packageId = isset($data['package_id']) && is_numeric($data['package_id']) ? (int) $data['package_id'] : null;

        if ($packageId !== null && class_exists(\App\Models\Package::class) && Schema::hasTable('packages')) {
            $stored = \App\Models\Package::query()->find($packageId);

            if ($stored !== null && is_numeric($stored->price) && (float) $stored->price > 0) {
                $price = (float) $stored->price;
            }
        }

        $project = trim((string) ($data['project'] ?? '')) ?: 'Default';
        $prefix = (string) ($item['price_prefix'] ?? '');
        $quantity = min(10, max(1, (int) ($data['quantity'] ?? 1)));
        $line = $this->lineKey($slug, 'package', 'package');
        $items = $this->sessionItems();
        $quantity = $prefix !== ''
            ? 1
            : min(10, ((int) ($items[$line]['quantity'] ?? 0)) + $quantity);

        $items[$line] = [
            'line' => $line,
            'kind' => 'package',
            'domain' => $slug,
            'display_name' => (string) $item['label'],
            'site' => $slug,
            'website_id' => null,
            'package_id' => $packageId,
            'package_slug' => $slug,
            'niche' => 'Packages',
            'project' => $project,
            'order_type' => 'package',
            'order_label' => $slug === 'display-enterprise'
                ? 'Custom campaign (We review your goals and confirm the quotation.)'
                : 'Backlink campaign (We place the links and send the report.)',
            'order_help' => $slug === 'display-enterprise'
                ? 'We review your goals and confirm the quotation before work starts.'
                : 'We place the backlinks and send the completion report.',
            'product_key' => 'package',
            'addon' => false,
            'content_topic' => 'General',
            'price' => $price,
            'price_prefix' => $prefix,
            'quantity' => $quantity,
            'backlinks' => $story['count'],
            'link_validity' => $story['delivery'],
            'link_type' => 'Included',
            'article_length' => (string) $item['size'],
            'stat_labels' => ['Backlinks', 'Delivery', 'Report', 'Package size'],
            'rules' => $story['gets'],
            'base_price' => $price,
        ];

        session(['shopping_cart' => $items]);

        return back()
            ->with('open_cart', true)
            ->with('shopping_status', $item['label'].' was added to your cart.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'line' => ['nullable', 'string', 'max:64'],
            'domain' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'lines' => ['nullable', 'array'],
            'lines.*.project' => ['nullable', 'string', 'max:80'],
            'lines.*.quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $items = $this->sessionItems();

        if (is_array($data['lines'] ?? null) && $data['lines'] !== []) {
            foreach ($data['lines'] as $key => $line) {
                if (! isset($items[$key]) || ! is_array($line)) {
                    continue;
                }

                $project = trim((string) ($line['project'] ?? ''));
                $items[$key]['project'] = $project !== '' ? $project : 'Default';

                if (isset($line['quantity'])) {
                    $items[$key]['quantity'] = (int) $line['quantity'];
                }
            }

            session(['shopping_cart' => $items]);

            return redirect()->route('shopping.index')->with('shopping_status', 'Cart updated.');
        }

        $key = $this->locate($items, $data);

        if ($key === null || ! isset($data['quantity'])) {
            return redirect()->route('shopping.index')->with('shopping_error', 'That item is not in the cart.');
        }

        $items[$key]['quantity'] = (int) $data['quantity'];
        session(['shopping_cart' => $items]);

        return redirect()->route('shopping.index')->with('shopping_status', 'Cart updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'line' => ['nullable', 'string', 'max:64'],
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        $items = $this->sessionItems();
        $key = $this->locate($items, $data);

        if ($key !== null) {
            unset($items[$key]);
        }

        session(['shopping_cart' => $items]);

        return back()
            ->with('open_cart', true)
            ->with('shopping_status', 'Removed from your cart.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget(['shopping_cart', 'shopping_coupon']);

        return redirect()->route('shopping.index')->with('shopping_status', 'Your cart is empty.');
    }

    public function coupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:40']]);

        if (! Schema::hasTable('coupons')) {
            return redirect()->route('shopping.index')->with('shopping_error', 'Coupons are not available right now.');
        }

        $code = Str::upper(trim($data['coupon_code']));
        $coupon = Coupon::query()->where('code', $code)->first();
        $subtotal = $this->sessionSubtotal();
        $user = $request->user();

        $valid = $coupon !== null && ($user !== null
            ? $coupon->isAvailableTo($user, $subtotal)
            : ($coupon->is_active && ! $coupon->expires_at?->isPast()
                && $coupon->customer_id === null
                && $subtotal >= (float) $coupon->minimum_order_amount));

        if (! $valid) {
            return back()
                ->withInput()
                ->with('shopping_error', 'This coupon is not valid for this cart.');
        }

        session(['shopping_coupon' => $coupon->code]);

        return back()->with('shopping_status', 'Coupon '.$coupon->code.' applied.');
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('shopping_coupon');

        return back()->with('shopping_status', 'Coupon removed.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $items = collect($this->sessionItems())
            ->filter(fn ($item) => is_array($item) && filled($item['domain'] ?? null) && is_numeric($item['price'] ?? null))
            ->values();

        if ($items->isEmpty()) {
            return redirect()->route('shopping.index')->with('shopping_error', 'Add an item before checking out.');
        }

        if ($request->user() === null) {
            session()->put('url.intended', route('shopping.checkout'));
            $login = Route::has('auth.login') ? 'auth.login' : (Route::has('login') ? 'login' : null);

            return $login
                ? redirect()->route($login)->with('status', 'Sign in to finish your order. Your cart is saved.')
                : redirect()->route('shopping.index')->with('shopping_error', 'Sign in to finish your order.');
        }

        $subtotal = $this->sessionSubtotal();
        $discount = $this->discountFor($subtotal);

        return view('customer.shopping.checkout', [
            'items' => $items,
            'quantity' => (int) $items->sum(fn (array $item) => (int) ($item['quantity'] ?? 1)),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount),
            'coupon' => session('shopping_coupon'),
            'loginHref' => Route::has('auth.login') ? route('auth.login') : url('/login'),
        ]);
    }

    public function placeCheckout(Request $request): RedirectResponse
    {
        $sessionItems = collect($this->sessionItems())
            ->filter(fn ($item) => is_array($item) && filled($item['domain'] ?? null) && is_numeric($item['price'] ?? null))
            ->values();

        if ($sessionItems->isEmpty()) {
            return redirect()->route('shopping.index')->with('shopping_error', 'Add an item before checking out.');
        }

        if ($request->user() === null) {
            session()->put('url.intended', route('shopping.checkout'));

            return redirect()->route(Route::has('auth.login') ? 'auth.login' : 'shopping.index')
                ->with('status', 'Sign in to finish your order. Your cart is saved.');
        }

        if (! Schema::hasTable('orders') || ! Schema::hasTable('order_items')) {
            return redirect()->route('shopping.checkout')->with('shopping_error', 'Checkout is not available right now.');
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.line' => ['required', 'string', 'max:64'],
            'items.*.target_url' => ['required', 'url', 'max:2048'],
            'items.*.anchor_text' => ['required', 'string', 'max:255'],
            'items.*.article_topic' => ['required', 'string', 'max:255'],
            'items.*.requirements' => ['nullable', 'string', 'max:5000'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
        ]);

        $detailsByLine = collect($data['items'])->keyBy('line');

        foreach ($sessionItems as $item) {
            $line = (string) ($item['line'] ?? '');
            if ($line === '' || ! $detailsByLine->has($line)) {
                throw ValidationException::withMessages([
                    'items' => 'Please complete the requirements for every cart item.',
                ]);
            }
        }

        if (filled($data['coupon_code'] ?? null)) {
            $code = Str::upper(trim((string) $data['coupon_code']));
            session(['shopping_coupon' => $code]);
        }

        $subtotal = $this->sessionSubtotal();
        $discount = $this->discountFor($subtotal);
        $couponCode = session('shopping_coupon');

        try {
            $order = DB::transaction(function () use ($request, $sessionItems, $detailsByLine, $subtotal, $discount, $couponCode) {
                $coupon = null;

                if (is_string($couponCode) && $couponCode !== '' && Schema::hasTable('coupons')) {
                    $coupon = Coupon::query()->where('code', $couponCode)->lockForUpdate()->first();

                    if ($coupon === null || ! $coupon->isAvailableTo($request->user(), $subtotal)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'This coupon is no longer available.',
                        ]);
                    }
                }

                $order = Order::query()->create([
                    'order_number' => $this->uniqueOrderNumber(),
                    'customer_id' => $request->user()->id,
                    'session_id' => $request->session()->getId(),
                    'total_amount' => max(0, $subtotal - $discount),
                    'discount_amount' => $discount,
                    'coupon_code' => $coupon?->code,
                ]);

                foreach ($sessionItems as $item) {
                    $line = (string) $item['line'];
                    $details = $detailsByLine->get($line);
                    $name = ($item['kind'] ?? '') === 'package'
                        ? (string) ($item['display_name'] ?? 'Backlink package')
                        : 'Publish Guest Post on '.$item['domain'].' · '.($item['order_label'] ?? 'Publish my article').' · '.($item['content_topic'] ?? 'General');

                    $order->items()->create([
                        'website_id' => $item['website_id'] ?? null,
                        'service_id' => null,
                        'item_name' => $name,
                        'unit_price' => (float) $item['price'],
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'credit_cost' => (int) ($item['credits'] ?? 0) * (int) ($item['quantity'] ?? 1),
                        'target_url' => $details['target_url'],
                        'anchor_text' => $details['anchor_text'],
                        'article_topic' => $details['article_topic'],
                        'requirements' => $details['requirements'] ?? null,
                    ]);
                }

                if ($coupon !== null && Schema::hasTable('coupon_usages')) {
                    CouponUsage::query()->create([
                        'coupon_id' => $coupon->id,
                        'customer_id' => $request->user()->id,
                        'order_id' => $order->id,
                        'discount_amount' => $discount,
                    ]);
                }

                if (Schema::hasTable('carts')) {
                    $cart = Cart::query()
                        ->where('customer_id', $request->user()->id)
                        ->where('status', 'active')
                        ->first();

                    if ($cart !== null) {
                        $cart->update([
                            'status' => 'converted',
                            'coupon_code' => $coupon?->code,
                            'coupon_discount' => $discount,
                        ]);
                    }
                }

                return $order;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            return redirect()->route('shopping.checkout')
                ->withInput()
                ->with('shopping_error', 'We could not create your order. Please try again.');
        }

        session()->forget(['shopping_cart', 'shopping_coupon']);

        if (Route::has('orders.payment')) {
            return redirect()->route('orders.payment', $order)
                ->with('success', 'Order created. Complete payment to begin processing.');
        }

        return redirect()->route('shopping.index')
            ->with('shopping_status', 'Order '.$order->order_number.' created.');
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'BM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Schema::hasTable('orders') && Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function catalog(NicheListingService $listings): Collection
    {
        $payload = $listings->catalogRows();
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

        return collect($rows)->filter(
            fn ($row) => is_array($row) && filled($row['domain'] ?? null)
        )->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function find(NicheListingService $listings, string $domain): ?array
    {
        $key = mb_strtolower(trim($domain));

        $row = $this->catalog($listings)->first(
            fn (array $item) => mb_strtolower((string) ($item['domain'] ?? '')) === $key
        );

        return is_array($row) ? $row : null;
    }

    /**
     * Refresh the stored price of each line while keeping the details chosen on the publish page.
     *
     * @param  Collection<int, array<string, mixed>>  $catalog
     * @return Collection<int, array<string, mixed>>
     */
    private function items(Collection $catalog): Collection
    {
        $clean = [];

        foreach ($this->sessionItems() as $key => $item) {
            if (! is_array($item) || ! filled($item['domain'] ?? null)) {
                continue;
            }

            if (! isset($item['line'])) {
                $item = $this->upgrade($item);
                $key = $item['line'];
            }

            if (($item['kind'] ?? '') !== 'package') {
                $domain = mb_strtolower((string) $item['domain']);
                $match = $catalog->first(
                    fn (array $row) => mb_strtolower((string) ($row['domain'] ?? '')) === $domain
                );

                if (is_array($match) && is_numeric($match['price'] ?? null)) {
                    $item['price'] = $this->unitPrice($item['base_price'] ?? $match['price'], $item['addon'] ?? false);
                    $item['dr'] = $match['dr'] ?? ($item['dr'] ?? null);
                }
            }

            $clean[$key] = $item;
        }

        session(['shopping_cart' => $clean]);

        return collect(array_values($clean));
    }

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    private function fromOffer(array $offer): array
    {
        $screenshot = $offer['screenshots'][0] ?? null;

        return [
            'domain' => (string) $offer['domain'],
            'display_name' => (string) $offer['display_name'],
            'site' => (string) $offer['key'],
            'website_id' => $offer['website_id'],
            'niche' => (string) ($offer['primary_category'] ?? ''),
            'language' => (string) ($offer['language'] ?? ''),
            'dr' => $offer['metrics']['dr'] ?? null,
            'credits' => $offer['credits'] ?? null,
            'base_price' => is_numeric($offer['price']) ? (float) $offer['price'] : null,
            'backlinks' => $offer['backlinks_allowed'] ?? null,
            'link_validity' => (string) ($offer['link_validity'] ?? 'Permanent'),
            'link_type' => (string) ($offer['link_type'] ?? 'DoFollow'),
            'article_length' => (string) ($offer['article_length'] ?? '—'),
            'tat_days' => $offer['tat_days'] ?? null,
            'rules' => array_values($offer['rules'] ?? []),
            'restricted' => $offer['restricted'] ?? [],
            'screenshot' => is_string($screenshot) ? $screenshot : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function fromRow(array $row): array
    {
        $niche = (string) (($row['niches'][0] ?? $row['niche'] ?? '') ?: '');

        return [
            'domain' => (string) $row['domain'],
            'display_name' => (string) $row['domain'],
            'site' => (string) ($row['id'] ?? $row['domain']),
            'website_id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : null,
            'niche' => $niche,
            'language' => (string) ($row['language'] ?? ''),
            'dr' => $row['dr'] ?? null,
            'credits' => $row['credits'] ?? null,
            'base_price' => is_numeric($row['price'] ?? null) ? (float) $row['price'] : null,
            'backlinks' => $row['backlinks_allowed'] ?? null,
            'link_validity' => 'Permanent',
            'link_type' => (string) ($row['link_type'] ?? 'DoFollow'),
            'article_length' => (string) ($row['article_length'] ?? '—'),
            'tat_days' => $row['tat_days'] ?? null,
            'rules' => $niche !== '' ? ["Content must be relevant to {$niche} or closely related topics."] : [],
            'restricted' => [],
            'screenshot' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $type
     * @return array<string, mixed>
     */
    private function build(string $line, array $details, array $type, string $topic, string $project, int $quantity): array
    {
        $addon = ! empty($type['addon']);

        return array_merge($details, [
            'line' => $line,
            'project' => $project,
            'order_type' => (string) $type['key'],
            'order_label' => (string) $type['label'],
            'order_help' => (string) ($type['help'] ?? ''),
            'product_key' => (string) ($type['product_key'] ?? 'guest_post'),
            'addon' => $addon,
            'content_topic' => $topic,
            'price' => $this->unitPrice($details['base_price'], $addon),
            'quantity' => max(1, min(20, $quantity)),
        ]);
    }

    /**
     * Turn a line saved by the old cart (keyed by domain only) into a full line.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function upgrade(array $item): array
    {
        $type = $this->orderType(null);
        $topic = 'General';
        $details = $this->fromRow($item);
        $line = $this->lineKey($details['domain'], $type['key'], $topic);

        return $this->build($line, $details, $type, $topic, 'Default', (int) ($item['quantity'] ?? 1));
    }

    /**
     * @return array<string, mixed>
     */
    private function orderType(?string $key): array
    {
        $groups = config('marketplace.order_types', []);
        $all = array_merge(...array_values(is_array($groups) ? $groups : []));

        foreach ($all as $type) {
            if (($type['key'] ?? null) === $key) {
                return $type;
            }
        }

        return $all[0] ?? ['key' => 'publish_only', 'label' => 'Publish my article', 'help' => '', 'product_key' => 'guest_post', 'addon' => false];
    }

    private function topic(?string $topic): string
    {
        $topics = config('marketplace.content_topics', []);
        $topic = trim((string) $topic);

        return in_array($topic, $topics, true) ? $topic : 'General';
    }

    private function unitPrice(?float $base, bool $addon): ?float
    {
        if ($base === null) {
            return null;
        }

        $extra = config('marketplace.writing_addon_price');

        return $addon && is_numeric($extra) ? $base + (float) $extra : $base;
    }

    private function lineKey(string $domain, string $type, string $topic): string
    {
        return md5(mb_strtolower($domain).'|'.$type.'|'.$topic);
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     * @param  array<string, mixed>  $data
     */
    private function locate(array $items, array $data): ?string
    {
        if (filled($data['line'] ?? null) && isset($items[$data['line']])) {
            return $data['line'];
        }

        $domain = mb_strtolower(trim((string) ($data['domain'] ?? '')));

        foreach ($items as $key => $item) {
            if ($domain !== '' && mb_strtolower((string) ($item['domain'] ?? '')) === $domain) {
                return (string) $key;
            }
        }

        return null;
    }

    private function sessionSubtotal(): float
    {
        return (float) collect($this->sessionItems())
            ->filter(fn ($item) => is_numeric($item['price'] ?? null))
            ->sum(fn ($item) => (float) $item['price'] * (int) ($item['quantity'] ?? 1));
    }

    private function discountFor(float $subtotal): float
    {
        $code = session('shopping_coupon');

        if (! is_string($code) || $code === '' || ! Schema::hasTable('coupons')) {
            return 0.0;
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null) {
            session()->forget('shopping_coupon');

            return 0.0;
        }

        $discount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        return min($subtotal, round($discount, 2));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function sessionItems(): array
    {
        $items = session('shopping_cart', []);

        return is_array($items) ? $items : [];
    }
}
