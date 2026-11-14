<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Models\Website;
use App\Notifications\CheckoutNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const DEMO_CATALOG = [
        'guest_post' => ['name' => 'Guest Post Placement', 'price' => 120, 'credits' => 40],
        'niche_edit' => ['name' => 'Niche Edit', 'price' => 80, 'credits' => 25],
        'link_insertion' => ['name' => 'Link Insertion', 'price' => 60, 'credits' => 20],
    ];

    public function cart(Request $request): View|JsonResponse
    {
        $cart = $this->cartFor($request);
        $subtotal = $this->cartSubtotal($cart);

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'items' => $cart->items,
                'coupon_code' => $cart->coupon_code,
                'subtotal' => $subtotal,
                'discount' => (float) $cart->coupon_discount,
                'total' => max(0, $subtotal - (float) $cart->coupon_discount),
            ]]);
        }

        return view('orders.cart', [
            'cart' => $cart,
            'catalog' => self::DEMO_CATALOG,
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - (float) $cart->coupon_discount),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_key' => ['required', Rule::in(array_keys(self::DEMO_CATALOG))],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'website_id' => ['nullable', 'integer', Rule::exists('websites', 'id')->where('status', 'active')],
        ]);

        $cart = $this->cartFor($request);
        $product = self::DEMO_CATALOG[$data['product_key']];
        $website = isset($data['website_id']) ? Website::query()->findOrFail($data['website_id']) : null;
        $itemName = $website ? $website->website_name.' — '.$product['name'] : $product['name'];
        $itemPrice = $website?->price ?? $product['price'];
        $itemCreditCost = $website?->credit_cost ?? $product['credits'];
        $item = $cart->items()->where('item_name', $itemName)->first();

        if ($item) {
            $item->increment('quantity', $data['quantity']);
        } else {
            $cart->items()->create([
                'item_name' => $itemName,
                'website_id' => $website?->id,
                'unit_price' => $itemPrice,
                'credit_cost' => $itemCreditCost,
                'quantity' => $data['quantity'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $cart->fresh('items')->items], 201);
        }

        if (! $request->user()) {
            return redirect()->guest(route('auth.login'))->with('url.intended', route('orders.checkout'));
        }

        return back()->with('success', 'Item added to your cart.');
    }

    public function updateCartItem(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->update($request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]));

        if ($request->expectsJson()) {
            return response()->json(['data' => $cartItem->fresh()]);
        }

        return back()->with('success', 'Cart quantity updated.');
    }

    public function removeCartItem(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Item removed from cart.']);
        }

        return back()->with('success', 'Item removed from your cart.');
    }

    public function applyCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:40']]);
        $cart = $this->cartFor($request);
        $coupon = Coupon::query()->where('code', Str::upper(trim($data['coupon_code'])))->first();
        $subtotal = $this->cartSubtotal($cart);

        if (! $coupon || ! $coupon->isAvailableTo($request->user(), $subtotal)
            || ($coupon->id !== (int) $cart->checkout_coupon_id && str_starts_with($coupon->code, 'CHECKOUT-'))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This coupon is not valid for this purchase.'], 422);
            }

            return back()->withErrors(['coupon_code' => 'This coupon is not valid for this purchase.']);
        }

        $discount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        $cart->update(['coupon_code' => $coupon->code, 'coupon_discount' => min($subtotal, $discount)]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['coupon_code' => $coupon->code, 'discount' => min($subtotal, $discount)]]);
        }

        return back()->with('success', 'Coupon applied.');
    }

    public function removeCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $this->cartFor($request)->update(['coupon_code' => null, 'coupon_discount' => 0]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Coupon removed.']);
        }

        return back()->with('success', 'Coupon removed.');
    }

    public function checkout(Request $request): View|RedirectResponse|JsonResponse
    {
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your cart is empty.'], 422);
            }

            return redirect()->route('orders.cart')->with('error', 'Add an item before checking out.');
        }

        $subtotal = $this->cartSubtotal($cart);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['items' => $cart->items, 'subtotal' => $subtotal, 'discount' => (float) $cart->coupon_discount, 'total' => max(0, $subtotal - (float) $cart->coupon_discount)]]);
        }

        return view('orders.checkout', ['cart' => $cart, 'subtotal' => $subtotal, 'total' => max(0, $subtotal - (float) $cart->coupon_discount)]);
    }

    public function emailCheckoutCoupon(Request $request): RedirectResponse
    {
        $cart = $this->cartFor($request);
        abort_if($cart->items->isEmpty(), 422);

        $coupon = DB::transaction(function () use ($cart, $request): Coupon {
            $lockedCart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            abort_unless($lockedCart->status === 'active' && $lockedCart->items()->exists(), 409);

            $coupon = Coupon::find($lockedCart->checkout_coupon_id);
            if ($coupon?->isAvailableTo($request->user(), $this->cartSubtotal($lockedCart))) {
                return $coupon;
            }

            $coupon = Coupon::create([
                'code' => 'CHECKOUT-'.Str::upper(Str::random(16)),
                'customer_id' => $request->user()->id,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'usage_limit' => 1,
                'per_customer_limit' => 1,
                'expires_at' => now()->addDay(),
                'is_active' => true,
            ]);
            $lockedCart->update(['checkout_coupon_id' => $coupon->id]);

            return $coupon;
        });

        $sent = rescue(function () use ($coupon, $request): bool {
            $request->user()->notify(new CheckoutNotification($coupon));

            return ! in_array(config('mail.default'), ['log', 'array'], true);
        }, false);

        return back()->with($sent ? 'success' : 'error', $sent
            ? 'Check your email for your checkout coupon.'
            : 'The website could not send the coupon. You can try again or continue to payment.');
    }

    public function placeOrder(Request $request): RedirectResponse|JsonResponse
    {
        return $this->createOrder($request, $request->validate($this->publishingRules())['items']);
    }

    public function startPayment(Request $request): RedirectResponse|JsonResponse
    {
        return $this->createOrder($request, []);
    }

    private function publishingRules(): array
    {
        return [
            'items' => ['required', 'array', 'list', 'min:1'],
            'items.*.target_url' => ['required', 'url:http,https', 'max:2048'],
            'items.*.anchor_text' => ['required', 'string', 'max:255'],
            'items.*.article_topic' => ['required', 'string', 'max:255'],
            'items.*.requirements' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function createOrder(Request $request, array $details): RedirectResponse|JsonResponse
    {
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) {
            return redirect()->route('orders.cart')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($request, $cart, $details): Order {
            $cart = Cart::query()->lockForUpdate()->findOrFail($cart->id)->load('items');
            if ($cart->status !== 'active' || $cart->items->isEmpty()
                || ($details !== [] && count($details) !== $cart->items->count())) {
                throw ValidationException::withMessages(['items' => 'Your cart changed. Review it before continuing.']);
            }

            $subtotal = $this->cartSubtotal($cart);
            $coupon = $cart->coupon_code ? Coupon::query()->where('code', $cart->coupon_code)->lockForUpdate()->first() : null;

            if ($cart->coupon_code && (! $coupon || ! $coupon->isAvailableTo($request->user(), $subtotal)
                || (str_starts_with($coupon->code, 'CHECKOUT-') && $coupon->id !== (int) $cart->checkout_coupon_id))) {
                throw ValidationException::withMessages(['coupon_code' => 'This coupon is no longer available.']);
            }

            $discount = $coupon
                ? ($coupon->discount_type === 'percentage' ? $subtotal * ((float) $coupon->discount_value / 100) : (float) $coupon->discount_value)
                : 0;
            $discount = min($subtotal, $discount);
            $order = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'customer_id' => $request->user()->id,
                'session_id' => $request->session()->getId(),
                'total_amount' => $subtotal - $discount,
                'discount_amount' => $discount,
                'coupon_code' => $coupon?->code,
            ]);
            foreach ($cart->items as $index => $cartItem) {
                $itemDetails = $details[$index] ?? [];
                $order->items()->create([
                    'website_id' => $cartItem->website_id, 'service_id' => $cartItem->service_id,
                    'item_name' => $cartItem->item_name, 'unit_price' => $cartItem->unit_price, 'quantity' => $cartItem->quantity,
                    'credit_cost' => $cartItem->credit_cost * $cartItem->quantity,
                    'target_url' => $itemDetails['target_url'] ?? '', 'anchor_text' => $itemDetails['anchor_text'] ?? '',
                    'article_topic' => $itemDetails['article_topic'] ?? '', 'requirements' => $itemDetails['requirements'] ?? null,
                ]);
            }
            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'customer_id' => $request->user()->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
            }
            $cart->update(['status' => 'converted', 'session_id' => (string) Str::uuid()]);

            return $order;
        });

        if ($request->expectsJson()) {
            return response()->json(['data' => $order->load('items'), 'payment_url' => route('orders.payment', $order)], 201);
        }

        return redirect()->route('orders.payment', $order);
    }

    public function submitDetails(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        $details = $request->validate($this->publishingRules())['items'];

        DB::transaction(function () use ($order, $details): void {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($order->payment_status === 'paid' && $order->order_status === 'awaiting_details', 409);
            $items = $order->items()->orderBy('id')->get();
            if ($items->count() !== count($details)) {
                throw ValidationException::withMessages(['items' => 'Enter publishing details for each item.']);
            }

            foreach ($items as $index => $item) {
                $item->update($details[$index]);
            }

            $order->update(['order_status' => 'awaiting_approval']);
        });

        rescue(fn () => $request->user()->notify(new CheckoutNotification($order)));
        User::query()->where('status', 'active')->whereRelation('role', 'role_name', 'Admin')
            ->each(fn (User $admin) => rescue(fn () => $admin->notify(new CheckoutNotification($order, forAdmin: true))));

        return back()->with('success', 'Publishing details submitted. Your order is ready for admin review.');
    }

    public function index(Request $request): View|JsonResponse
    {
        $orders = $this->ordersFor($request)->latest()->paginate(15);

        if ($request->expectsJson()) {
            return response()->json(['data' => $orders]);
        }

        return view('orders.order-history', compact('orders'));
    }

    public function show(Request $request, Order $order): View|JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($request->expectsJson()) {
            return response()->json(['data' => $order->load('items', 'payments')]);
        }

        return view('orders.order-detail', ['order' => $order->load(['items' => fn ($query) => $query->orderBy('id'), 'invoice', 'rewardCoupon'])]);
    }

    private function cartFor(Request $request): Cart
    {
        $customerId = $request->user()?->id;
        $cart = Cart::query()
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->when($customerId === null, fn ($query) => $query->where('session_id', $request->session()->getId()))
            ->orderByRaw('case when session_id = ? then 0 else 1 end', [$request->session()->getId()])
            ->first();

        if (! $cart) {
            $cart = Cart::firstOrCreate(
                ['session_id' => $request->session()->getId()],
                ['customer_id' => $customerId, 'status' => 'active']
            );
        }

        abort_unless((int) $cart->customer_id === (int) $customerId, 403);

        if ($cart->status !== 'active') {
            $cart->items()->delete();
            $cart->update(['status' => 'active', 'coupon_code' => null, 'coupon_discount' => 0, 'checkout_coupon_id' => null]);
        }

        if ($cart->session_id !== $request->session()->getId()) {
            $cart->update(['session_id' => $request->session()->getId()]);
        }

        return $cart->load('items');
    }

    private function ordersFor(Request $request)
    {
        return Order::with('items')->where('customer_id', $request->user()->id);
    }

    private function cartSubtotal(Cart $cart): float
    {
        return (float) $cart->items->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->quantity);
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'BM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
