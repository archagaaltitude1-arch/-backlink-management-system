<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\CreditPricing;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $packages = Package::query()
            ->when($search !== '', fn ($query) => $query->where('package_name', 'like', "%{$search}%"))
            ->orderBy('package_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'search' => $search,
            'total' => Package::query()->count(),
            'creditPricing' => CreditPricing::query()->orderBy('credit_amount')->get(),
            'activePricingCount' => CreditPricing::query()->where('status', 'active')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', ['package' => new Package]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = Package::query()->create($request->validated());

        return redirect()->route('admin.packages.show', $package)->with('success', 'Package added.');
    }

    public function show(Package $package): View
    {
        return view('admin.packages.show', ['package' => $package]);
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.form', ['package' => $package]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $package->update($request->validated());

        return redirect()->route('admin.packages.show', $package)->with('success', 'Package updated.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.packages')->with('success', 'Package deleted.');
    }
}
