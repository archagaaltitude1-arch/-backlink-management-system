<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreditPricingRequest;
use App\Models\CreditPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditPricingController extends Controller
{
    public function create(): View
    {
        return view('admin.credit-pricing.form', ['pricing' => new CreditPricing]);
    }

    public function store(CreditPricingRequest $request): RedirectResponse
    {
        CreditPricing::query()->create($request->validated());

        return redirect()->route('admin.packages')->with('success', 'Credit price added.');
    }

    public function edit(CreditPricing $creditPricing): View
    {
        return view('admin.credit-pricing.form', ['pricing' => $creditPricing]);
    }

    public function update(CreditPricingRequest $request, CreditPricing $creditPricing): RedirectResponse
    {
        $creditPricing->update($request->validated());

        return redirect()->route('admin.packages')->with('success', 'Credit price updated.');
    }

    public function destroy(CreditPricing $creditPricing): RedirectResponse
    {
        $creditPricing->delete();

        return redirect()->route('admin.packages')->with('success', 'Credit price deleted.');
    }
}
