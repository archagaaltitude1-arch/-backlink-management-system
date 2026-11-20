<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Rules\MaxKeywords;
use App\Services\Customer\NicheListingService;
use App\Services\Customer\PublishOfferService;
use App\Support\CheckoutItemRules;
use App\Support\CouponCart;
use App\Support\CouponReservation;
use App\Support\CustomerDiscounts;
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
        $quote = $this->pricedQuote();
        $discount = $quote['offer_discount'];

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
            'packageDiscount' => $quote['package_discount'],
            'offerLabel' => $quote['offer_label'],
            'discount' => $discount,
            'total' => $quote['total'],
            'coupon' => $quote['coupon_code'] ?? session('shopping_coupon'),
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
            'backlinks' => ['nullable', 'integer', 'min:1', 'max:500'],
            'order_type' => ['nullable', 'string', 'max:60'],
            'content_topic' => ['nullable', 'string', 'max:80'],
            'project' => ['nullable', 'string', 'max:80'],
            'websites' => ['nullable', 'array', 'max:250'],
            'websites.*' => ['string', 'max:255'],
            'website_selection' => ['nullable', 'boolean'],
        ]);

        if (filled($data['package_slug'] ?? null)) {
            return $this->storePackage($data, $listings);
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

        return redirect()
            ->route('shopping.checkout')
            ->with('shopping_status', 'Publish Guest Post on '.$details['display_name'].' was added to your cart.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storePackage(array $data, NicheListingService $listings): RedirectResponse
    {
        $slug = (string) $data['package_slug'];
        $item = PublicPackages::find($slug);
        $story = PublicPackages::story($slug);

        if ($item === null || $story === null) {
            return redirect()->route('packages.index')->with('shopping_error', 'That package is not available.');
        }

        if (PublicPackages::serviceKey($slug) !== null && isset($data['backlinks'])) {
            if (! empty($data['website_selection'])) {
                return $this->storeTieredWebsites($data, $item, $story, $listings);
            }

            return $this->storeTieredPackage($data, $item, $story);
        }

        $postedQuantity = min(10, max(1, (int) ($data['quantity'] ?? 1)));
        $chosen = $this->chosenPackageWebsites(
            $slug,
            $story,
            $postedQuantity,
            $data['websites'] ?? [],
            isset($data['order_type']) ? (string) $data['order_type'] : null,
            $listings,
        );

        if ($chosen instanceof RedirectResponse) {
            return $chosen;
        }

        $price = isset($item['amount']) && is_numeric($item['amount']) ? (float) $item['amount'] : null;
        $packageId = isset($data['package_id']) && is_numeric($data['package_id']) ? (int) $data['package_id'] : null;

        if ($packageId !== null && class_exists(Package::class) && Schema::hasTable('packages')) {
            $stored = Package::query()->find($packageId);

            if ($stored !== null && is_numeric($stored->price) && (float) $stored->price > 0) {
                $price = (float) $stored->price;
            }
        }

        $project = trim((string) ($data['project'] ?? '')) ?: 'Default';
        $prefix = (string) ($item['price_prefix'] ?? '');
        $line = $this->lineKey($slug, 'package', 'package');
        $items = $this->sessionItems();
        $existingSites = is_array($items[$line]['websites'] ?? null) ? $items[$line]['websites'] : [];
        $quantity = $prefix !== ''
            ? 1
            : min(10, ((int) ($items[$line]['quantity'] ?? 0)) + $postedQuantity);
        $websites = $prefix !== ''
            ? $chosen
            : array_values(array_unique([...$existingSites, ...$chosen]));
        $order = $this->selectedPackageOrder($slug, $story, (string) ($data['order_type'] ?? ''));

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
            'order_type' => $order['value'],
            'order_label' => $order['label'],
            'order_help' => $order['help'],
            'product_key' => 'package',
            'addon' => false,
            'content_topic' => 'General',
            'price' => $price,
            'price_prefix' => $prefix,
            'quantity' => $quantity,
            'backlinks' => $story['count'],
            'websites' => $websites,
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

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $story
     */
    private function storeTieredPackage(array $data, array $item, array $story): RedirectResponse
    {
        $slug = (string) $data['package_slug'];
        $count = (int) $data['backlinks'];
        $band = PublicPackages::bandForSlug($slug);

        if (! PublicPackages::backlinksAllowed($slug, $count)) {
            throw ValidationException::withMessages([
                'backlinks' => $band === null
                    ? 'Choose a valid package quantity.'
                    : 'Choose between '.$band['min'].' and '.$band['max'].' backlinks for this package.',
            ]);
        }

        $quote = PublicPackages::quote($slug, $count);
        $order = $this->selectedPackageOrder($slug, $story, (string) ($data['order_type'] ?? ''));
        $line = $this->lineKey($slug, 'package', 'tier');
        $items = $this->sessionItems();
        $items[$line] = [
            'line' => $line,
            'kind' => 'package',
            'domain' => $slug,
            'display_name' => (string) $item['label'],
            'site' => $slug,
            'website_id' => null,
            'package_id' => null,
            'package_slug' => $slug,
            'niche' => 'Packages',
            'project' => 'Default',
            'order_type' => $order['value'],
            'order_label' => $order['label'],
            'order_help' => $order['help'],
            'product_key' => 'package',
            'addon' => false,
            'content_topic' => 'General',
            'price' => $quote['final'],
            'price_prefix' => '',
            'quantity' => 1,
            'backlinks' => $quote['quantity'],
            'package_discount' => $quote['discount_amount'],
            'websites' => [],
            'link_validity' => $story['delivery'] ?? '',
            'link_type' => 'Included',
            'article_length' => $quote['quantity'].' backlinks',
            'stat_labels' => ['Backlinks', 'Delivery', 'Report', 'Package size'],
            'rules' => $story['gets'] ?? [],
            'base_price' => $quote['final'],
        ];
        session(['shopping_cart' => $items]);

        return redirect()
            ->route('packages.show', $slug)
            ->with('shopping_status', $item['label'].' is ready. Choose '.$quote['quantity'].' websites next.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $story
     */
    private function storeTieredWebsites(array $data, array $item, array $story, NicheListingService $listings): RedirectResponse
    {
        $slug = (string) $data['package_slug'];
        $count = (int) $data['backlinks'];
        $back = route('packages.websites', ['slug' => $slug, 'backlinks' => $count]);

        if (! PublicPackages::backlinksAllowed($slug, $count)) {
            return redirect()->to($back)->with('shopping_error', 'That quantity is not available for this package.');
        }

        $known = [];

        foreach ($listings->catalogRows()['rows'] as $row) {
            $domain = trim((string) ($row['domain'] ?? ''));

            if ($domain !== '') {
                $known[mb_strtolower($domain)] = $domain;
            }
        }

        $chosen = [];

        foreach (is_array($data['websites'] ?? null) ? $data['websites'] : [] as $website) {
            $key = mb_strtolower(trim((string) $website));

            if ($key === '' || isset($chosen[$key])) {
                continue;
            }

            if (! isset($known[$key])) {
                return redirect()->to($back)->with('shopping_error', 'That website is not on the list.');
            }

            $chosen[$key] = $known[$key];
        }

        $websites = array_values($chosen);

        if (count($websites) !== $count) {
            return redirect()->to($back)->with('shopping_error', 'Choose exactly '.$count.' websites for this package.');
        }

        $this->storeTieredPackage($data, $item, $story);
        $items = $this->sessionItems();
        $line = $this->lineKey($slug, 'package', 'tier');

        if (isset($items[$line]) && is_array($items[$line])) {
            $items[$line]['websites'] = $websites;
            $items[$line]['backlinks'] = $count;
            session(['shopping_cart' => $items]);
        }

        return redirect()
            ->route('shopping.checkout')
            ->with('shopping_status', $count.' websites saved for '.$item['label'].'.');
    }

    /**
     * @param  array<string, mixed>  $story
     * @return array<int, string>|RedirectResponse
     */
    private function chosenPackageWebsites(string $slug, array $story, int $quantity, mixed $postedSites, ?string $orderType, NicheListingService $listings): array|RedirectResponse
    {
        $known = [];

        foreach ($listings->catalogRows()['rows'] as $row) {
            $domain = trim((string) ($row['domain'] ?? ''));

            if ($domain === '') {
                continue;
            }

            $known[mb_strtolower($domain)] = $domain;
        }

        $chosen = [];

        foreach (is_array($postedSites) ? $postedSites : [] as $website) {
            $key = mb_strtolower(trim((string) $website));

            if ($key === '' || isset($chosen[$key])) {
                continue;
            }

            if (! isset($known[$key])) {
                return $this->websiteSelectionRedirect($slug, $quantity, $orderType, 'That website is not on the list.');
            }

            $chosen[$key] = $known[$key];
        }

        $websites = array_values($chosen);
        $expected = PublicPackages::websiteLimit($story, $quantity);

        if ($websites === []) {
            return $this->websiteSelectionRedirect($slug, $quantity, $orderType, 'Choose the websites for this package before adding it to your cart.');
        }

        if ($expected !== null && count($websites) !== $expected) {
            return $this->websiteSelectionRedirect($slug, $quantity, $orderType, 'Choose '.$expected.' websites for this package.');
        }

        if ($expected === null && count($websites) > 50) {
            return $this->websiteSelectionRedirect($slug, $quantity, $orderType, 'Choose up to 50 websites for this package.');
        }

        return $websites;
    }

    private function websiteSelectionRedirect(string $slug, int $quantity, ?string $orderType, string $message): RedirectResponse
    {
        $query = ['slug' => $slug, 'quantity' => $quantity];

        if (filled($orderType)) {
            $query['order_type'] = $orderType;
        }

        return redirect()
            ->route('packages.websites', $query)
            ->with('shopping_error', $message);
    }

    /**
     * @param  array<string, mixed>  $story
     * @return array{value: string, label: string, help: string}
     */
    private function selectedPackageOrder(string $slug, array $story, string $posted): array
    {
        $types = is_array($story['order_types'] ?? null) ? $story['order_types'] : [];

        foreach ($types as $type) {
            if (! is_array($type)) {
                continue;
            }

            if ((string) ($type['value'] ?? '') === $posted) {
                return [
                    'value' => (string) $type['value'],
                    'label' => (string) $type['label'],
                    'help' => (string) ($type['help'] ?? $type['label']),
                ];
            }
        }

        if ($types !== [] && is_array($types[0])) {
            return [
                'value' => (string) ($types[0]['value'] ?? 'package'),
                'label' => (string) ($types[0]['label'] ?? 'Backlink campaign'),
                'help' => (string) ($types[0]['help'] ?? $types[0]['label'] ?? ''),
            ];
        }

        $custom = $slug === 'display-enterprise';

        return [
            'value' => 'package',
            'label' => $custom
                ? 'Custom campaign (We review your goals and confirm the quotation.)'
                : 'Backlink campaign (We place the links and send the report.)',
            'help' => $custom
                ? 'We review your goals and confirm the quotation before work starts.'
                : 'We place the backlinks and send the completion report.',
        ];
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
        session()->forget(['shopping_cart', 'shopping_coupon', 'shopping_coupon_skipped']);

        return redirect()->route('shopping.index')->with('shopping_status', 'Your cart is empty.');
    }

    public function coupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:40']]);

        if (! Schema::hasTable('coupons')) {
            return redirect()->route('shopping.index')->with('shopping_error', 'Coupons are not available right now.');
        }

        $coupon = Coupon::findByCode($data['coupon_code']);
        $subtotal = $this->sessionSubtotal();
        $message = $coupon?->rejectionFor($request->user(), $subtotal, CouponCart::fromShoppingItems($this->sessionItems()))
            ?? ($coupon === null ? 'Invalid coupon code.' : null);

        if ($message !== null) {
            return back()
                ->withInput()
                ->with('shopping_error', $message)
                ->withErrors(['coupon_code' => $message]);
        }

        session()->forget('shopping_coupon_skipped');
        session(['shopping_coupon' => $coupon->code]);

        return back()->with('shopping_status', 'Coupon '.$coupon->code.' applied.');
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('shopping_coupon');
        session(['shopping_coupon_skipped' => true]);

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

        $quote = $this->pricedQuote($request->user());
        $choices = $this->couponChoices($request->user(), (float) $quote['subtotal']);
        $applied = $quote['coupon_code'];
        $hasOpenChoice = $choices->contains(fn (Coupon $coupon): bool => $coupon->code !== $applied);

        return view('customer.shopping.checkout', [
            'items' => $items,
            'quantity' => (int) $items->sum(fn (array $item) => (int) ($item['quantity'] ?? 1)),
            'subtotal' => $quote['subtotal'],
            'packageDiscount' => $quote['package_discount'],
            'offerLabel' => $quote['offer_label'],
            'discount' => $quote['offer_discount'],
            'total' => $quote['total'],
            'coupon' => $applied,
            'couponOffer' => $hasOpenChoice || $applied !== null
                ? null
                : app(CouponReservation::class)->offer(
                    $request->user(),
                    (float) $quote['subtotal'],
                    CouponCart::fromShoppingItems($this->sessionItems()),
                ),
            'orderRewards' => app(CustomerDiscounts::class)->orderRewards($request->user()),
            'couponChoices' => $choices,
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
            ...CheckoutItemRules::forItems(),
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

        $couponCode = session('shopping_coupon');

        try {
            $order = DB::transaction(function () use ($request, $sessionItems, $detailsByLine, $couponCode) {
                $context = CouponCart::fromShoppingItems($sessionItems->all());
                $subtotal = $this->sessionSubtotal();

                if (is_string($couponCode) && $couponCode !== '' && Schema::hasTable('coupons')) {
                    $found = Coupon::findByCode($couponCode);
                    $coupon = $found === null ? null : Coupon::query()->whereKey($found->id)->lockForUpdate()->first();
                    $message = $coupon === null
                        ? 'Invalid coupon code.'
                        : $coupon->rejectionFor($request->user(), $subtotal, $context);

                    if ($message !== null) {
                        throw ValidationException::withMessages([
                            'coupon_code' => $message,
                        ]);
                    }
                }

                $quote = app(CustomerDiscounts::class)->quote(
                    $request->user(),
                    $subtotal,
                    $this->sessionPackageDiscount(),
                    $context,
                    is_string($couponCode) ? $couponCode : null,
                );

                $order = Order::query()->create([
                    'order_number' => $this->uniqueOrderNumber(),
                    'customer_id' => $request->user()->id,
                    'session_id' => $request->session()->getId(),
                    'total_amount' => $quote['total'],
                    'discount_amount' => $quote['offer_discount'],
                    'package_discount_amount' => $quote['package_discount'],
                    'coupon_code' => $quote['coupon_code'],
                    'offer_type' => $quote['offer_type'],
                ]);

                foreach ($sessionItems as $item) {
                    $line = (string) $item['line'];
                    $details = $detailsByLine->get($line);
                    $name = ($item['kind'] ?? '') === 'package'
                        ? (string) ($item['display_name'] ?? 'Backlink package')
                        : 'Publish Guest Post on '.$item['domain'].' · '.($item['order_label'] ?? 'Write and publish for me').' · '.($item['content_topic'] ?? 'General');

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
                        'keywords' => MaxKeywords::normalize($details['keywords'] ?? null),
                        'requirements' => $details['requirements'] ?? null,
                        'backlinks' => isset($item['backlinks']) ? (int) $item['backlinks'] : null,
                        'selected_websites' => is_array($item['websites'] ?? null) && $item['websites'] !== [] ? array_values($item['websites']) : null,
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
                            'coupon_code' => $quote['coupon_code'],
                            'coupon_discount' => $quote['offer_discount'],
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

        session()->forget(['shopping_cart', 'shopping_coupon', 'shopping_coupon_skipped']);

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
            'rules' => $niche !== ''
                ? ["Our team writes and publishes the placement for {$niche} or a closely related topic."]
                : ['Our team writes and publishes the placement.'],
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

        return $all[0] ?? ['key' => 'write_and_publish', 'label' => 'Write and publish for me', 'help' => 'Our writers create the article, then we publish it.', 'product_key' => 'guest_post', 'addon' => false];
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

    /**
     * Coupons the customer can apply on this cart, plus any reward they saved for later.
     *
     * @return Collection<int, Coupon>
     */
    private function couponChoices(User $customer, float $subtotal): Collection
    {
        $saved = app(CustomerDiscounts::class)->orderRewards($customer)
            ->filter(fn (Coupon $coupon): bool => $coupon->saved_for_later_at !== null);

        if (! Schema::hasTable('coupons')) {
            return $saved->values();
        }

        $context = CouponCart::fromShoppingItems($this->sessionItems());
        $applicable = Coupon::query()
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('usages')
            ->latest()
            ->get()
            ->filter(fn (Coupon $coupon): bool => $coupon->rejectionFor($customer, $subtotal, $context) === null);

        return $saved->concat($applicable)->unique('id')->values();
    }

    private function sessionSubtotal(): float
    {
        return (float) collect($this->sessionItems())
            ->filter(fn ($item) => is_numeric($item['price'] ?? null))
            ->sum(fn ($item) => (float) $item['price'] * (int) ($item['quantity'] ?? 1));
    }

    /**
     * @return array{original: float, package_discount: float, subtotal: float, offer_type: ?string, offer_label: string, coupon_code: ?string, offer_discount: float, total: float}
     */
    private function pricedQuote(?User $customer = null): array
    {
        $code = session('shopping_coupon');
        $quote = app(CustomerDiscounts::class)->quote(
            $customer ?? request()->user(),
            $this->sessionSubtotal(),
            $this->sessionPackageDiscount(),
            CouponCart::fromShoppingItems($this->sessionItems()),
            is_string($code) ? $code : null,
        );

        if (is_string($code) && $code !== '' && $quote['coupon_code'] === null) {
            session()->forget('shopping_coupon');
        }

        return $quote;
    }

    private function sessionPackageDiscount(): float
    {
        return (float) collect($this->sessionItems())
            ->sum(fn ($item): float => (float) ($item['package_discount'] ?? 0) * (int) ($item['quantity'] ?? 1));
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
