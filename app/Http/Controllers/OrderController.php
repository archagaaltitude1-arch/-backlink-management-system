<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function cart(Request $request): View
    {
        $cart = $this->cartFor($request);
        return view('orders.cart', ['cart' => $cart, 'total' => $cart->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity)]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:150'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'website_id' => ['nullable', 'integer'],
            'service_id' => ['nullable', 'integer'],
        ]);

        $cart = $this->cartFor($request);
        $item = $cart->items()->where('item_name', $data['item_name'])->first();
        $item ? $item->increment('quantity', $data['quantity']) : $cart->items()->create($data);

        return back()->with('success', 'Item added to your cart.');
    }

    public function updateCartItem(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->update($request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]));
        return back()->with('success', 'Cart quantity updated.');
    }

    public function removeCartItem(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->cartFor($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->delete();
        return back()->with('success', 'Item removed from your cart.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) return redirect()->route('orders.cart')->with('error', 'Add an item before checking out.');
        return view('orders.checkout', ['cart' => $cart, 'total' => $cart->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity)]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_url' => ['required', 'url', 'max:2048'],
            'items.*.anchor_text' => ['required', 'string', 'max:255'],
            'items.*.article_topic' => ['required', 'string', 'max:255'],
            'items.*.requirements' => ['nullable', 'string', 'max:5000'],
        ]);
        $cart = $this->cartFor($request);
        if ($cart->items->isEmpty()) return redirect()->route('orders.cart')->with('error', 'Your cart is empty.');

        $order = DB::transaction(function () use ($request, $cart, $data) {
            $order = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'customer_id' => auth()->id(),
                'session_id' => $request->session()->getId(),
                'total_amount' => $cart->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity),
            ]);
            foreach ($cart->items as $index => $cartItem) {
                $details = $data['items'][$index];
                $order->items()->create([
                    'website_id' => $cartItem->website_id, 'service_id' => $cartItem->service_id,
                    'item_name' => $cartItem->item_name, 'unit_price' => $cartItem->unit_price, 'quantity' => $cartItem->quantity,
                    'target_url' => $details['target_url'], 'anchor_text' => $details['anchor_text'],
                    'article_topic' => $details['article_topic'], 'requirements' => $details['requirements'] ?? null,
                ]);
            }
            $cart->update(['status' => 'converted']);
            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function index(Request $request): View
    {
        $orders = $this->ordersFor($request)->latest()->get();
        return view('orders.order-history', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($this->ordersFor($request)->whereKey($order->id)->exists(), 404);
        return view('orders.order-detail', compact('order'));
    }

    private function cartFor(Request $request): Cart
    {
        $cart = Cart::firstOrCreate(['session_id' => $request->session()->getId()], ['customer_id' => auth()->id(), 'status' => 'active']);

        if ($cart->status !== 'active') {
            $cart->update(['status' => 'active']);
        }

        return $cart->load('items');
    }

    private function ordersFor(Request $request)
    {
        return Order::with('items')->where('session_id', $request->session()->getId());
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'BM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());
        return $number;
    }
}