<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NicheController extends Controller
{
    public function __construct(private NicheListingService $listings) {}

    public function index(): View
    {
        $niches = [];

        foreach ($this->listings->catalog() as $name) {
            $niches[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'total' => $this->listings->countFor($name),
            ];
        }

        return view('customer.niches.index', [
            'niches' => $niches,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $page = $this->listings->forNiche($slug, $request->query());
        abort_if($page === null, 404);

        return view('customer.niches.show', $page);
    }
}
