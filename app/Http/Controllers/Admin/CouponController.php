<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\DiscountCampaign;
use App\Models\FirstOrderRedemption;
use App\Models\User;
use App\Notifications\CouponNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $view = $request->string('view')->toString();
        $search = trim($request->string('q')->toString());

        $coupons = Coupon::query()
            ->where('code', 'not like', 'ORD-%')
            ->withSum('usages as discount_total', 'discount_amount')
            ->withCount('usages')
            ->when($search !== '', fn ($query) => $query->where('code', 'like', '%'.strtoupper($search).'%'))
            ->when($view === 'active', fn ($query) => $query->where('is_active', true)->where(function ($rows): void {
                $rows->whereNull('expires_at')->orWhere('expires_at', '>', now());
            }))
            ->when($view === 'expired', fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'search' => $search,
            'view' => $view,
            'campaigns' => DiscountCampaign::query()->orderBy('id')->get(),
            'redemptions' => FirstOrderRedemption::query()->with('customer')->latest()->limit(12)->get(),
            'total' => Coupon::query()->where('code', 'not like', 'ORD-%')->count(),
            'activeCount' => Coupon::query()->where('code', 'not like', 'ORD-%')->where('is_active', true)->count(),
            'discountTotal' => (float) Coupon::query()->join('coupon_usages', 'coupon_usages.coupon_id', '=', 'coupons.id')->sum('coupon_usages.discount_amount')
                + (float) FirstOrderRedemption::query()->sum('discount_amount'),
        ]);
    }

    public function updateCampaign(Request $request, DiscountCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'is_enabled' => ['required', 'boolean'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'combines_with_package_discount' => ['nullable', 'boolean'],
            'qualifying_orders' => ['nullable', 'integer', 'min:1'],
            'recurring' => ['nullable', 'boolean'],
            'inactivity_days' => ['nullable', 'integer', Rule::in([30, 60, 90])],
            'expiry_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        if ($data['discount_type'] === 'percentage' && (float) $data['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'A percentage discount cannot be more than 100.'])->withInput();
        }

        $campaign->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'minimum_order_amount' => $data['minimum_order_amount'],
            'combines_with_package_discount' => $campaign->type === 'first_order'
                ? $request->boolean('combines_with_package_discount')
                : $campaign->combines_with_package_discount,
            'qualifying_orders' => $campaign->type === 'loyalty' ? ($data['qualifying_orders'] ?? null) : $campaign->qualifying_orders,
            'recurring' => $campaign->type === 'loyalty' ? $request->boolean('recurring') : $campaign->recurring,
            'inactivity_days' => $campaign->type === 'comeback' ? ($data['inactivity_days'] ?? null) : $campaign->inactivity_days,
            'expiry_days' => $campaign->type === 'first_order' ? null : ($data['expiry_days'] ?? $campaign->expiry_days),
        ]);

        return back()->with('success', 'Campaign updated.');
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon(['code' => Coupon::uniqueCode(), 'purchase_type' => 'both', 'per_customer_limit' => 1, 'is_active' => true, 'minimum_order_amount' => 0]),
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::query()->create($request->couponAttributes());

        return redirect()->route('admin.coupons.show', $coupon)->with('success', 'Coupon '.$coupon->code.' created.');
    }

    public function show(Coupon $coupon): View
    {
        $coupon->load(['usages.coupon', 'emailLogs.customer']);
        $usages = $coupon->usages()->with('customer')->latest()->get();

        return view('admin.coupons.show', [
            'coupon' => $coupon,
            'usages' => $usages,
            'customers' => User::query()->where('account_type', 'customer')->orderBy('name')->get(['id', 'name', 'email']),
            'discountTotal' => (float) $usages->sum('discount_amount'),
        ]);
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', ['coupon' => $coupon]);
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->couponAttributes());

        return redirect()->route('admin.coupons.show', $coupon)->with('success', 'Coupon updated.');
    }

    public function activate(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    }

    public function send(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:selected,unpaid,all'],
            'customers' => ['required_if:audience,selected', 'array'],
            'customers.*' => ['integer', 'exists:users,id'],
        ]);

        $customers = User::query()
            ->where('account_type', 'customer')
            ->when($data['audience'] === 'selected', fn ($query) => $query->whereIn('id', $data['customers'] ?? []))
            ->when($data['audience'] === 'unpaid', fn ($query) => $query->whereHas(
                'orders',
                fn ($orders) => $orders->where('payment_status', '!=', 'paid'),
            ))
            ->get();

        if ($customers->isEmpty()) {
            return back()->with('error', $data['audience'] === 'unpaid'
                ? 'No customers are waiting to pay.'
                : 'Choose at least one registered customer.');
        }

        if ($customers->count() === 1) {
            $coupon->update(['customer_id' => $customers->first()->id]);
        }

        foreach ($customers as $customer) {
            $customer->notify(new CouponNotice(
                'A coupon is available',
                'Use '.$coupon->code.' for '.$coupon->discountSummary().($coupon->expires_at ? ' It expires '.$coupon->expires_at->format('M j, Y').'.' : '.'),
            ));
        }

        return back()->with('success', 'Coupon added to '.$customers->count().' customer dashboard'.($customers->count() === 1 ? '' : 's').'. No email was sent.');
    }
}
