<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\PublishOfferService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

class PublishController extends Controller
{
    public function __construct(private PublishOfferService $offers) {}

    public function show(string $site): View
    {
        set_time_limit(120);

        $offer = $this->offers->resolve($site);
        abort_if($offer === null, 404);

        $user = auth()->user();
        $return = url()->current();
        $loginHref = Route::has('account.start')
            ? route('account.start', ['tab' => 'login', 'redirect' => $return])
            : (Route::has('auth.login') ? route('auth.login') : url('/login'));

        $canReview = $user !== null && $this->offers->canReview($user, $offer);
        $reviewState = 'guest';

        if ($user !== null) {
            $reviewState = $canReview ? 'eligible' : 'blocked';
        }

        return view('customer.publish.show', [
            'offer' => $offer,
            'loginHref' => $loginHref,
            'cartAction' => Route::has('orders.cart.items.store') ? route('orders.cart.items.store') : null,
            'saveAction' => Route::has('customer.saved-websites.store') ? route('customer.saved-websites.store') : null,
            'reviewAction' => route('publish.reviews.store', $offer['key']),
            'canReview' => $canReview,
            'reviewState' => $reviewState,
        ]);
    }
}
