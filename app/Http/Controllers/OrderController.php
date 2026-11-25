<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Website;
use App\Rules\MaxKeywords;
use App\Support\CheckoutItemRules;
use App\Support\CouponCart;
use App\Support\CustomerDiscounts;
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
        $quote = $this->discountQuote($request, $cart);

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'items' => $cart->items,
                'coupon_code' => $quote['coupon_code'],
                'subtotal' => $quote['subtotal'],
                'discount' => $quote['offer_discount'],
                'total' => $quote['total'],
            ]]);
        }

        return view('orders.cart', [
            'cart' => $cart,
            'catalog' => self::DEMO_CATALOG,
            'subtotal' => $quote['subtotal'],
            'offer' => $quote,
            'total' => $quote['total'],
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

        if (app(CustomerDiscounts::class)->awaitingFirstOrder($request->user())) {
            $message = 'Your first order uses the 10% new-customer offer.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['coupon_code' => $message]);
        }

        $cart = $this->cartFor($request);
        $coupon = Coupon::findByCode($data['coupon_code']);
        $subtotal = $this->cartSubtotal($cart);
        $message = $coupon === null
            ? 'Invalid coupon code.'
            : $coupon->rejectionFor($request->user(), $subtotal, CouponCart::fromNamedItems($cart->items));

        if ($message !== null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['coupon_code' => $message]);
        }

        $discount = $coupon->discountAmount($subtotal);

        $cart->update(['coupon_code' => $coupon->code, 'coupon_discount' => $discount]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['coupon_code' => $coupon->code, 'discount' => $discount]]);
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

        $quote = $this->discountQuote($request, $cart);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['items' => $cart->items, 'subtotal' => $quote['subtotal'], 'discount' => $quote['offer_discount'], 'total' => $quote['total']]]);
        }

        return view('orders.checkout', ['cart' => $cart, 'offer' => $quote, 'total' => $quote['total']]);
    }

    public function placeOrder(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            ...CheckoutItemRules::forItems(),
        ]);
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) {
            return redirect()->route('orders.cart')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($request, $cart, $data) {
            $subtotal = $this->cartSubtotal($cart);
            $context = CouponCart::fromNamedItems($cart->items);

            if ($cart->coupon_code && ! app(CustomerDiscounts::class)->awaitingFirstOrder($request->user())) {
                $found = Coupon::findByCode($cart->coupon_code);
                $coupon = $found === null ? null : Coupon::query()->whereKey($found->id)->lockForUpdate()->first();
                $message = $coupon === null
                    ? 'Invalid coupon code.'
                    : $coupon->rejectionFor($request->user(), $subtotal, $context);

                if ($message !== null) {
                    throw ValidationException::withMessages(['coupon_code' => $message]);
                }
            }

            $quote = app(CustomerDiscounts::class)->quote($request->user(), $subtotal, 0, $context, $cart->coupon_code);
            $order = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'customer_id' => $request->user()->id,
                'session_id' => $request->session()->getId(),
                'total_amount' => $quote['total'],
                'discount_amount' => $quote['offer_discount'],
                'package_discount_amount' => $quote['package_discount'],
                'coupon_code' => $quote['coupon_code'],
                'offer_type' => $quote['offer_type'],
            ]);
            foreach ($cart->items as $index => $cartItem) {
                $details = $data['items'][$index];
                $order->items()->create([
                    'website_id' => $cartItem->website_id, 'service_id' => $cartItem->service_id,
                    'item_name' => $cartItem->item_name, 'unit_price' => $cartItem->unit_price, 'quantity' => $cartItem->quantity,
                    'credit_cost' => $cartItem->credit_cost * $cartItem->quantity,
                    'target_url' => $details['target_url'], 'anchor_text' => $details['anchor_text'],
                    'article_topic' => $details['article_topic'],
                    'keywords' => MaxKeywords::normalize($details['keywords'] ?? null),
                    'requirements' => $details['requirements'] ?? null,
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

        return view('orders.order-detail', ['order' => $order->load('items', 'invoice', 'rewardCoupon')]);
    }

    public function thanks(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($order->payment_status !== 'paid' && $request->query('stripe') !== 'returned') {
            return redirect()->route('orders.payment', $order);
        }

        return view('orders.thanks', [
            'order' => $order->load(['items', 'invoice', 'rewardCoupon.usages']),
            'shopMore' => app(CustomerDiscounts::class)->shopMoreMessage($request->user()),
        ]);
    }

    /**
     * @return array{original: float, package_discount: float, subtotal: float, offer_type: ?string, offer_label: string, coupon_code: ?string, offer_discount: float, total: float}
     */
    private function discountQuote(Request $request, Cart $cart): array
    {
        return app(CustomerDiscounts::class)->quote(
            $request->user(),
            $this->cartSubtotal($cart),
            0,
            CouponCart::fromNamedItems($cart->items),
            $cart->coupon_code,
        );
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

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'BM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
