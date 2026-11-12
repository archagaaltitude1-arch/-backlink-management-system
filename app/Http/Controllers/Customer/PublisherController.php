<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\NicheListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class PublisherController extends Controller
{
    public function __construct(private NicheListingService $listings) {}

    public function index(): View
    {
        set_time_limit(120);

        $catalog = $this->listings->catalogRows();
        $rows = collect($catalog['rows'])
            ->sortByDesc(fn (array $row) => $row['dr'] ?? -1)
            ->values()
            ->all();

        $categories = [];

        foreach ($this->listings->categoryNames() as $name) {
            $slug = Str::slug($name);

            if ($name !== '' && $slug !== '') {
                $categories[$name] = $slug;
            }
        }

        ksort($categories);

        return view('customer.publishers.index', [
            'demo' => $catalog['demo'],
            'rows' => $rows,
            'categories' => $categories,
        ]);
    }
}
