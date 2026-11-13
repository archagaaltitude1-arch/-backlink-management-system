<?php

namespace App\Http\Controllers;

use App\Models\BillingAddress;
use App\Models\CreditPackage;
use App\Models\CustomerProject;
use App\Models\Invoice;
use App\Models\OrderAssignment;
use App\Models\PaymentMethod;
use App\Models\SavedWebsite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $customer = $request->user();
        $wallet = $customer->wallet()->firstOrCreate([], ['available_balance' => 0, 'reserved_balance' => 0]);
        $orders = $customer->orders()->with('items')->latest()->take(5)->get();
        $projects = $customer->hasMany(CustomerProject::class, 'customer_id')->latest()->take(3)->get();

        return view('customer.dashboard', [
            'customer' => $customer,
            'wallet' => $wallet,
            'orders' => $orders,
            'projects' => $projects,
            'savedCard' => PaymentMethod::query()->where('customer_id', $customer->id)->orderByDesc('is_default')->latest()->first(),
            'orderCount' => $customer->orders()->count(),
            'savedWebsiteCount' => $customer->savedWebsites()->count(),
            'unreadNotificationCount' => $customer->unreadNotifications()->count(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('customer.profile', ['customer' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $customer = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer)],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);
        $customer->update($data);

        if ($request->expectsJson()) {
            return response()->json(['data' => $customer->fresh()->only(['id', 'name', 'email', 'phone'])]);
        }

        return back()->with('success', 'Account details updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password updated.');
    }

    public function wallet(Request $request): View
    {
        $wallet = $request->user()->wallet()->firstOrCreate([], ['available_balance' => 0, 'reserved_balance' => 0]);
        $transactions = $wallet->transactions()->latest()->paginate(10);
        $packages = CreditPackage::query()->where('is_active', true)->orderBy('credits')->get();

        return view('customer.wallet', compact('wallet', 'transactions', 'packages'));
    }

    public function paymentMethods(Request $request): View
    {
        $paymentMethods = $request->user()->hasMany(PaymentMethod::class, 'customer_id')->latest()->get();

        return view('customer.payment-methods', compact('paymentMethods'));
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', Rule::in(['Visa', 'Mastercard', 'American Express'])],
            'last_four' => ['required', 'digits:4'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
        $customer = $request->user();

        if ($request->boolean('is_default')) {
            $customer->hasMany(PaymentMethod::class, 'customer_id')->update(['is_default' => false]);
        }

        $customer->hasMany(PaymentMethod::class, 'customer_id')->create([
            'label' => $data['brand'].' ending in '.$data['last_four'],
            'brand' => $data['brand'],
            'last_four' => $data['last_four'],
            'token_reference' => 'demo_'.Str::random(32),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Demo payment method saved. No card number was stored.');
    }

    public function destroyPaymentMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        abort_unless($paymentMethod->customer_id === $request->user()->id, 404);
        $paymentMethod->delete();

        return back()->with('success', 'Payment method removed.');
    }

    public function invoices(Request $request): View
    {
        $invoices = $request->user()->hasMany(Invoice::class, 'customer_id')->with('order')->latest('issued_at')->paginate(10);

        return view('customer.invoices', compact('invoices'));
    }

    public function downloadInvoice(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->customer_id === $request->user()->id, 404);

        return response()->view('orders.invoice', compact('invoice'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$invoice->invoice_number.'.html"');
    }

    public function addresses(Request $request): View
    {
        $addresses = $request->user()->billingAddresses()->latest()->get();

        return view('customer.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'full_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'size:2'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
        $customer = $request->user();

        if ($request->boolean('is_default')) {
            $customer->billingAddresses()->update(['is_default' => false]);
        }

        $customer->billingAddresses()->create($data);

        return back()->with('success', 'Billing address saved.');
    }

    public function updateAddress(Request $request, BillingAddress $address): RedirectResponse|JsonResponse
    {
        abort_unless($address->customer_id === $request->user()->id, 404);
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'full_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'size:2'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->billingAddresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        if ($request->expectsJson()) {
            return response()->json(['data' => $address->fresh()]);
        }

        return back()->with('success', 'Billing address updated.');
    }

    public function destroyAddress(Request $request, BillingAddress $address): RedirectResponse
    {
        abort_unless($address->customer_id === $request->user()->id, 404);
        $address->delete();

        return back()->with('success', 'Billing address removed.');
    }

    public function savedWebsites(Request $request): View
    {
        $websites = $request->user()->savedWebsites()->latest()->get();

        return view('customer.saved-websites', compact('websites'));
    }

    public function storeSavedWebsite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'website_name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'domain_rating' => ['nullable', 'integer', 'between:0,100'],
            'monthly_traffic' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $request->user()->savedWebsites()->updateOrCreate(['domain' => $data['domain']], $data);

        return back()->with('success', 'Website saved to your account.');
    }

    public function destroySavedWebsite(Request $request, SavedWebsite $savedWebsite): RedirectResponse
    {
        abort_unless($savedWebsite->customer_id === $request->user()->id, 404);
        $savedWebsite->delete();

        return back()->with('success', 'Saved website removed.');
    }

    public function notifications(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('customer.notifications', compact('notifications'));
    }

    public function markNotificationRead(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function projects(Request $request): View
    {
        $projects = $request->user()->hasMany(CustomerProject::class, 'customer_id')->with('order')->latest()->paginate(10);

        return view('customer.projects', compact('projects'));
    }

    public function backlinks(Request $request): View
    {
        $backlinks = $request->user()->hasMany(CustomerProject::class, 'customer_id')
            ->with(['order', 'orderItem.article'])
            ->whereNotNull('published_url')
            ->latest()
            ->paginate(10);

        return view('customer.backlinks', compact('backlinks'));
    }

    public function decideBacklink(Request $request, CustomerProject $project): RedirectResponse
    {
        abort_unless($project->customer_id === $request->user()->id, 404);
        abort_unless($project->published_url !== null, 404);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'changes'])],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'approve') {
            $project->update([
                'verification_status' => 'approved',
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $this->syncAssignment($project, 'completed');
            $this->completeOrderWhenApproved($project);

            return back()->with('success', 'Backlink approved.');
        }

        $project->update([
            'verification_status' => 'changes_requested',
            'status' => 'processing',
            'report_summary' => $data['feedback'] ?? $project->report_summary,
            'completed_at' => null,
        ]);
        $this->syncAssignment($project, 'in_progress');

        return back()->with('success', 'Change request sent back to the assigned staff.');
    }

    private function syncAssignment(CustomerProject $project, string $status): void
    {
        if ($project->order_item_id === null) {
            return;
        }

        OrderAssignment::query()->where('order_item_id', $project->order_item_id)->update(['status' => $status]);
    }

    private function completeOrderWhenApproved(CustomerProject $project): void
    {
        $order = $project->order;
        if ($order === null) {
            return;
        }

        $itemIds = $order->items()->pluck('id');
        if ($itemIds->isEmpty()) {
            return;
        }

        $approved = CustomerProject::query()
            ->whereIn('order_item_id', $itemIds)
            ->where('verification_status', 'approved')
            ->count();

        if ($approved === $itemIds->count()) {
            $order->update(['order_status' => 'completed']);
        }
    }

    public function reports(Request $request): View
    {
        $reports = $request->user()->hasMany(CustomerProject::class, 'customer_id')
            ->whereNotNull('report_summary')->latest()->paginate(10);

        return view('customer.reports', compact('reports'));
    }

    public function apiProfile(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'name', 'email', 'phone'])]);
    }

    public function apiWallet(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet()->firstOrCreate([], ['available_balance' => 0, 'reserved_balance' => 0]);

        return response()->json(['data' => [
            'available_balance' => $wallet->available_balance,
            'reserved_balance' => $wallet->reserved_balance,
            'transactions' => $wallet->transactions()->latest()->paginate(15),
        ]]);
    }

    public function apiInvoices(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->hasMany(Invoice::class, 'customer_id')->with('order:id,order_number')->latest('issued_at')->paginate(10)]);
    }

    public function apiNotifications(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->notifications()->latest()->paginate(15)]);
    }

    public function apiSavedWebsites(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->savedWebsites()->latest()->paginate(20)]);
    }

    public function apiStoreAddress(Request $request): JsonResponse
    {
        $this->storeAddress($request);

        return response()->json(['data' => $request->user()->billingAddresses()->latest()->first()], 201);
    }

    public function apiStoreSavedWebsite(Request $request): JsonResponse
    {
        $this->storeSavedWebsite($request);

        return response()->json(['data' => $request->user()->savedWebsites()->latest()->first()], 201);
    }
}
