<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\WebsiteProfileService;
use App\Support\DemoNicheData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

class MarketplaceController extends Controller
{
    public function __construct(private WebsiteProfileService $profiles) {}

    public function show(string $website): View
    {
        if (ctype_digit($website)) {
            abort_unless(class_exists(\App\Models\Website::class) && Schema::hasTable('websites'), 404);

            $model = \App\Models\Website::query()->find($website);
            abort_if($model === null, 404);

            if (Schema::hasColumn('websites', 'status') && strtolower((string) ($model->status ?? '')) !== 'active') {
                abort(404);
            }

            $profile = $this->profiles->build($model);
        } else {
            $sample = (bool) config('marketplace.demo')
                || ! class_exists(\App\Models\Website::class)
                || ! Schema::hasTable('websites');

            abort_unless($sample, 404);

            $row = DemoNicheData::find($website);
            abort_if($row === null, 404);

            $profile = $this->profiles->build($row);
        }

        return view('customer.marketplace.show', [
            'profile' => $profile,
        ]);
    }
}
