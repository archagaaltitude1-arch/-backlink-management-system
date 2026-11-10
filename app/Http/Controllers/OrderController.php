<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Models\Website;
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

        if (! $coupon || ! $this->couponIsAvailable($coupon, $request->user(), $subtotal)) {
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

        return view('orders.checkout', ['cart' => $cart, 'total' => max(0, $subtotal - (float) $cart->coupon_discount)]);
    }

    public function placeOrder(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_url' => ['required', 'url', 'max:2048'],
            'items.*.anchor_text' => ['required', 'string', 'max:255'],
            'items.*.article_topic' => ['required', 'string', 'max:255'],
            'items.*.requirements' => ['nullable', 'string', 'max:5000'],
        ]);
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) {
            return redirect()->route('orders.cart')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($request, $cart, $data) {
            $subtotal = $this->cartSubtotal($cart);
            $coupon = $cart->coupon_code ? Coupon::query()->where('code', $cart->coupon_code)->lockForUpdate()->first() : null;

            if ($cart->coupon_code && (! $coupon || ! $this->couponIsAvailable($coupon, $request->user(), $subtotal))) {
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
                $details = $data['items'][$index];
                $order->items()->create([
                    'website_id' => $cartItem->website_id, 'service_id' => $cartItem->service_id,
                    'item_name' => $cartItem->item_name, 'unit_price' => $cartItem->unit_price, 'quantity' => $cartItem->quantity,
                    'credit_cost' => $cartItem->credit_cost * $cartItem->quantity,
                    'target_url' => $details['target_url'], 'anchor_text' => $details['anchor_text'],
                    'article_topic' => $details['article_topic'], 'requirements' => $details['requirements'] ?? null,
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
            $cart->update(['status' => 'converted']);

            return $order;
        });

        if ($request->expectsJson()) {
            return response()->json(['data' => $order->load('items'), 'payment_url' => route('orders.payment', $order)], 201);
        }

        return redirect()->route('orders.payment', $order)->with('success', 'Order created. Complete payment to begin processing.');
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

        return view('orders.order-detail', compact('order'));
    }

    private function cartFor(Request $request): Cart
    {
        $cart = Cart::query()
            ->where('customer_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (! $cart) {
            $cart = Cart::firstOrCreate(
                ['session_id' => $request->session()->getId()],
                ['customer_id' => $request->user()->id, 'status' => 'active']
            );
        }

        if ($cart->status !== 'active') {
            $cart->update(['status' => 'active']);
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

    private function couponIsAvailable(Coupon $coupon, User $customer, float $subtotal): bool
    {
        if (! $coupon->is_active || ($coupon->expires_at && $coupon->expires_at->isPast()) || $subtotal < (float) $coupon->minimum_order_amount) {
            return false;
        }

        if ($coupon->usage_limit !== null && $coupon->usages()->count() >= $coupon->usage_limit) {
            return false;
        }

        return $coupon->usages()->where('customer_id', $customer->id)->count() < $coupon->per_customer_limit;
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'BM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
