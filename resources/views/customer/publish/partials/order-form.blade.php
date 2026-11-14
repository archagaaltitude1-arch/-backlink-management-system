@php
    $placement = $offer['order_types']['content_placement'] ?? [];
    $insertion = $offer['order_types']['link_insertion'] ?? [];
    $topics = $offer['content_topics'] ?? [];
    $defaultType = $placement[0] ?? ['key' => 'publish_only', 'label' => 'Publish my article', 'product_key' => 'guest_post', 'addon' => false];
    $unit = is_numeric($offer['price']) ? (float) $offer['price'] : null;
    $line = $unit === null ? '—' : '$'.number_format($unit, 2);
    $cartPost = \Illuminate\Support\Facades\Route::has('shopping.store') ? route('shopping.store') : null;
@endphp
<section class="pb-order" aria-labelledby="order-title" data-order
    data-price="{{ $unit === null ? '' : $unit }}"
    data-addon="{{ $offer['writing_addon_price'] === null ? '' : $offer['writing_addon_price'] }}"
    data-name="{{ $offer['display_name'] }}"
    data-link="{{ $offer['link_type'] }}">
    <h2 id="order-title" class="sr-only">Order</h2>
    <form method="POST" action="{{ $cartPost ?? '#' }}" @unless ($cartPost) onsubmit="return false" @endunless>
        @if ($cartPost)
            @csrf
        @endif
        <input type="hidden" name="domain" value="{{ $offer['domain'] }}">
        <input type="hidden" name="site" value="{{ $offer['key'] }}">
        <input type="hidden" name="website_id" value="{{ $offer['website_id'] }}">
        <input type="hidden" name="product_key" value="{{ $defaultType['product_key'] }}" data-product-key>
        <input type="hidden" name="order_type" value="{{ $defaultType['key'] }}" data-order-type>
        <input type="hidden" name="order_label" value="{{ $defaultType['label'] }}" data-order-label>
        <input type="hidden" name="content_topic" value="General" data-topic-value>
        <input type="hidden" name="project" value="Default">
        <input type="hidden" name="quantity" value="1" data-qty-value>

        <fieldset class="pb-types">
            <legend>Order type *</legend>
            <div data-order-group="content_placement">
                @foreach ($placement as $type)
                    <label class="pb-choice">
                        <input type="radio" name="order_choice" value="{{ $type['key'] }}" data-product="{{ $type['product_key'] }}" data-addon="{{ ! empty($type['addon']) ? '1' : '0' }}" data-label="{{ $type['label'] }}" @checked($loop->first)>
                        <span><strong>{{ $type['label'] }}</strong> <span class="pb-help">({{ $type['help'] }})</span></span>
                    </label>
                @endforeach
            </div>
            <div data-order-group="link_insertion" hidden>
                @foreach ($insertion as $type)
                    <label class="pb-choice">
                        <input type="radio" name="order_choice" value="{{ $type['key'] }}" data-product="{{ $type['product_key'] }}" data-addon="{{ ! empty($type['addon']) ? '1' : '0' }}" data-label="{{ $type['label'] }}">
                        <span><strong>{{ $type['label'] }}</strong> <span class="pb-help">({{ $type['help'] }})</span></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="pb-topics">
            <legend>Your article topic *</legend>
            <div class="pb-topic-wrap">
                @foreach (array_slice($topics, 0, 6) as $topic)
                    <label class="pb-topic">
                        <input type="radio" name="topic_choice" value="{{ $topic }}" @checked($topic === 'General')>
                        <span>{{ $topic }}</span>
                    </label>
                @endforeach
                @if (count($topics) > 6)
                    <details class="pb-more">
                        <summary>More topics</summary>
                        <div class="pb-topic-wrap">
                            @foreach (array_slice($topics, 6) as $topic)
                                <label class="pb-topic">
                                    <input type="radio" name="topic_choice" value="{{ $topic }}">
                                    <span>{{ $topic }}</span>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </fieldset>

        <div class="pb-qty">
            <span id="qty-label">Quantity</span>
            <div>
                <button type="button" data-qty="-1" aria-label="Decrease quantity">−</button>
                <input type="number" min="1" max="10" value="1" inputmode="numeric" aria-labelledby="qty-label" data-qty-input>
                <button type="button" data-qty="1" aria-label="Increase quantity">+</button>
            </div>
        </div>

        <div class="pb-summary">
            <div class="pb-sum-row">
                <span data-line-label>1× Publish on {{ $offer['display_name'] }} – {{ $offer['link_type'] }} link</span>
                <span data-line-price>{{ $line }}</span>
            </div>
            <div class="pb-sum-row">
                <span data-type-label>Order type – {{ $defaultType['label'] }}</span>
                <span data-type-price>—</span>
            </div>
            <div class="pb-sum-row">
                <span data-topic-label>Article topic – General</span>
                <span>—</span>
            </div>
            <div class="pb-sum-row pb-subtotal">
                <span>Subtotal</span>
                <strong data-subtotal>{{ $line }}</strong>
            </div>
        </div>

        <p class="pb-note">After checkout you'll add your article or topic, anchor text and target URL on the requirements page.</p>

        @if ($cartPost)
            <button class="pb-cart" type="submit">Add to cart</button>
        @else
            <a class="pb-cart" href="{{ $loginHref }}">Sign in to order</a>
        @endif
        {{-- TODO(Anchuga): store order_type, content_topic, writing add-on on cart items; add text_link and contextual_link product keys. --}}
    </form>

    <ul class="pb-info">
        <li>
            <strong>Estimated delivery</strong>
            <span>{{ $offer['tat_days'] ?? '—' }} days to publication</span>
        </li>
        <li>
            <strong>Pay only for verified links</strong>
            <span>Reserved credits are released if a placement fails.</span>
        </li>
        <li>
            <strong>Questions?</strong>
            <span>Our team replies within [HOURS] hours. <a href="mailto:[YOUR EMAIL]">Contact us</a></span>
        </li>
    </ul>
</section>
