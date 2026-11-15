{{-- Sample reviews and portraits for the layout. Replace with real, approved customers before launch. --}}
@php
    $reviews = $reviews ?? [
        ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'Contextual links went onto checked publisher sites, and the live URL was in our report the same week.'],
        ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'Filtering by niche and DR made the shortlist simple. Every site we ordered was a real publisher.'],
        ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'Guest posts went live inside the delivery window, and the verified report was ready for our client.'],
        ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'Niche edits on older articles gave us a faster start than waiting for brand-new posts to index.'],
        ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'Traffic, spam score and price are on every listing, so we stopped guessing which sites were worth buying.'],
        ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'The white-label report was clean enough to send to our clients without rebuilding it.'],
    ];
@endphp

@once
    <style>
        .say-section {
            width: 100%;
            overflow: hidden;
            background: #F6F7F9;
            color: #152029;
        }
        .say-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            padding: 72px clamp(24px, 4vw, 56px) 0;
            text-align: center;
        }
        .say-kicker {
            margin: 0 0 10px;
            color: #C0340D;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .say-title {
            margin: 0 auto;
            max-width: 18em;
            font-size: clamp(32px, 4vw, 44px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .say-lead {
            margin: 14px auto 0;
            max-width: 46ch;
            color: #4A5560;
            font-size: 16px;
            line-height: 1.65;
        }
        .say-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: none;
        }
        .say-status {
            margin: 0;
            color: #4A5560;
            font-size: 13px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .say-nav { display: flex; gap: 8px; }
        .say-btn {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid #E3E5E8;
            border-radius: 999px;
            background: #fff;
            color: #152029;
            cursor: pointer;
        }
        .say-btn:hover:not(:disabled) { border-color: #152029; background: #152029; color: #fff; }
        .say-btn:disabled { opacity: 0.35; cursor: default; }
        .say-btn:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .say-stage {
            position: relative;
            overflow: hidden;
            margin: 0 clamp(24px, 4vw, 56px);
            padding: 28px 0 72px;
            container-type: inline-size;
        }
        .say-track {
            display: flex;
            gap: 20px;
            width: max-content;
            transition: transform 0.45s ease;
        }
        .say-card {
            display: flex;
            width: 100cqi;
            min-height: 280px;
            flex: none;
            flex-direction: column;
            align-items: center;
            margin: 0;
            text-align: center;
            border: 1px solid #E3E5E8;
            border-radius: 20px;
            background: #fff;
            padding: 28px 24px 22px;
            box-shadow: 0 10px 28px rgba(21, 32, 41, 0.05);
        }
        .say-mark {
            display: block;
            margin: 0 0 8px;
            color: #FF4917;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 42px;
            line-height: 0.8;
        }
        .say-bubble {
            margin: 0;
            color: #152029;
            font-size: 16px;
            line-height: 1.65;
        }
        .say-person {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-top: auto;
            padding-top: 22px;
            text-align: center;
        }
        .say-person img {
            width: 44px;
            height: 44px;
            flex: none;
            border-radius: 999px;
            object-fit: cover;
        }
        .say-name { margin: 0; font-size: 15px; font-weight: 800; }
        .say-role {
            margin: 2px 0 0;
            color: #4A5560;
            font-size: 13px;
            font-weight: 600;
        }
        @media (min-width: 900px) {
            .say-card { width: calc((100cqi - 60px) / 4); }
        }
        @media (min-width: 640px) and (max-width: 899px) {
            .say-card { width: calc((100cqi - 20px) / 2); }
        }
        @media (max-width: 800px) {
            .say-head {
                padding-top: 48px;
            }
            .say-stage { padding-bottom: 48px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .say-track { transition: none; }
        }
    </style>
@endonce

<section class="say-section" aria-labelledby="reviews-heading" aria-roledescription="carousel">
    <div class="say-head">
        <div>
            <p class="say-kicker">Testimonials</p>
            <h2 id="reviews-heading" class="say-title font-display">What our customers are saying</h2>
            <p class="say-lead">Campaign notes from teams who order placements through BacklinkPro.</p>
        </div>
        <div class="say-controls">
            <p class="say-status" data-review-status>1 / {{ count($reviews) }}</p>
            <div class="say-nav">
                <button type="button" class="say-btn" data-review-prev aria-label="Previous review">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 3 5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="say-btn" data-review-next aria-label="Next review">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="say-stage" data-review-stage>
        <div class="say-track" data-review-track>
            @foreach ($reviews as $review)
                <figure class="say-card" data-review-card>
                    <blockquote class="say-bubble">
                        <span class="say-mark" aria-hidden="true">“</span>
                        {{ $review['quote'] }}
                    </blockquote>
                    <figcaption class="say-person">
                        <img src="{{ asset('images/reviews/'.$review['photo']) }}" alt="" width="44" height="44">
                        <div>
                            <p class="say-name">{{ $review['name'] }}</p>
                            <p class="say-role">{{ $review['role'] }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.say-section').forEach(function (section) {
                    var track = section.querySelector('[data-review-track]');
                    var stage = section.querySelector('[data-review-stage]');
                    var cards = track ? Array.prototype.slice.call(track.querySelectorAll('[data-review-card]')) : [];
                    var prev = section.querySelector('[data-review-prev]');
                    var next = section.querySelector('[data-review-next]');
                    var status = section.querySelector('[data-review-status]');
                    var index = 0;

                    function step() {
                        if (!cards.length) return 0;
                        var styles = window.getComputedStyle(track);
                        var gap = parseFloat(styles.columnGap || styles.gap) || 20;
                        return cards[0].getBoundingClientRect().width + gap;
                    }

                    function visibleCount() {
                        if (!stage || !cards.length) return 1;
                        var width = cards[0].getBoundingClientRect().width;
                        var styles = window.getComputedStyle(track);
                        var gap = parseFloat(styles.columnGap || styles.gap) || 20;
                        return Math.max(1, Math.floor((stage.clientWidth + gap) / (width + gap)));
                    }

                    function maxIndex() {
                        return Math.max(0, cards.length - visibleCount());
                    }

                    function paint() {
                        if (!track || !cards.length) return;
                        var max = maxIndex();
                        if (index > max) index = max;
                        if (index < 0) index = 0;
                        track.style.transform = 'translateX(' + (-index * step()) + 'px)';
                        if (prev) prev.disabled = index <= 0;
                        if (next) next.disabled = index >= max;
                        if (status) {
                            var visible = visibleCount();
                            var start = index + 1;
                            var end = Math.min(cards.length, index + visible);
                            status.textContent = start === end ? (start + ' of ' + cards.length) : (start + '–' + end + ' of ' + cards.length);
                        }
                    }

                    if (next) next.addEventListener('click', function () { index += 1; paint(); });
                    if (prev) prev.addEventListener('click', function () { index -= 1; paint(); });
                    section.addEventListener('keydown', function (event) {
                        if (event.key === 'ArrowRight') { index += 1; paint(); }
                        if (event.key === 'ArrowLeft') { index -= 1; paint(); }
                    });
                    window.addEventListener('resize', paint);
                    paint();
                });
            });
        </script>
    @endpush
@endonce
