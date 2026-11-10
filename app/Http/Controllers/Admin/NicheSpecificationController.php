<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NicheSpecificationRequest;
use App\Models\NicheSpecification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NicheSpecificationController extends Controller
{
    public function create(): View
    {
        return view('admin.niche-specifications.form', [
            'specification' => new NicheSpecification,
        ]);
    }

    public function store(NicheSpecificationRequest $request): RedirectResponse
    {
        NicheSpecification::query()->create($request->validated());

        return redirect()->route('admin.niches')->with('success', 'Specification added.');
    }

    public function edit(NicheSpecification $nicheSpecification): View
    {
        return view('admin.niche-specifications.form', [
            'specification' => $nicheSpecification,
        ]);
    }

    public function update(NicheSpecificationRequest $request, NicheSpecification $nicheSpecification): RedirectResponse
    {
        $nicheSpecification->update($request->validated());

        return redirect()->route('admin.niches')->with('success', 'Specification updated.');
    }

    public function destroy(NicheSpecification $nicheSpecification): RedirectResponse
    {
        $nicheSpecification->delete();

        return redirect()->route('admin.niches')->with('success', 'Specification deleted.');
    }
}
