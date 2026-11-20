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
            display: grid;
            align-items: end;
            gap: 24px;
            padding: 72px clamp(24px, 4vw, 48px) 0;
            text-align: left;
        }
        .say-kicker {
            margin: 0 0 12px;
            color: #C0340D;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .say-title {
            margin: 0;
            max-width: 12em;
            font-size: clamp(36px, 4vw, 48px);
            line-height: 1.15;
            font-weight: 400;
        }
        .say-lead {
            margin: 0;
            max-width: 36ch;
            color: #4A5560;
            font-size: 15px;
            line-height: 1.7;
        }
        .say-aside { justify-self: start; }
        @media (min-width: 900px) {
            .say-head { grid-template-columns: minmax(0, 1.15fr) minmax(16rem, 0.7fr); }
            .say-aside { justify-self: end; }
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
            margin: 0;
            padding: 28px clamp(24px, 4vw, 48px) 72px;
            container-type: inline-size;
        }
        .say-track {
            display: flex;
            gap: 16px;
            align-items: stretch;
            width: max-content;
            transition: transform 0.45s ease;
        }
        .say-card {
            display: flex;
            width: 100cqi;
            flex: none;
            flex-direction: column;
            margin: 0;
            overflow: hidden;
            border: 1px solid #152029;
            border-radius: 16px;
            background: #152029;
            box-shadow: 0 10px 28px rgba(21, 32, 41, 0.12);
        }
        .say-photo {
            display: block;
            width: 100%;
            height: 208px;
            flex: none;
            object-fit: cover;
            object-position: center 18%;
        }
        .say-panel {
            display: flex;
            flex: 1;
            flex-direction: column;
            background: #152029;
            color: #fff;
            padding: 20px;
        }
        .say-bubble {
            margin: 0;
            color: #fff;
            font-size: 15px;
            line-height: 1.7;
        }
        .say-line { height: 1px; margin: auto 0 16px; background: rgba(255, 255, 255, 0.15); }
        .say-name { margin: 0; color: #fff; font-size: 15px; font-weight: 800; }
        .say-role {
            margin: 4px 0 0;
            color: #D5DCE3;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        @container (min-width: 1100px) {
            .say-card { width: calc((100cqi - 48px) / 4); }
        }
        @container (min-width: 640px) and (max-width: 1099px) {
            .say-card { width: calc((100cqi - 16px) / 2); }
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
        </div>
        <div class="say-aside">
            <p class="say-lead">Campaign notes from teams who order placements through BacklinkPro.</p>
            <div class="say-controls" style="margin-top: 16px;">
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
    </div>
    <div class="say-stage" data-review-stage>
        <div class="say-track" data-review-track>
            @foreach ($reviews as $review)
                <figure class="say-card" data-review-card>
                    <img class="say-photo" src="{{ asset('images/reviews/'.$review['photo']) }}" alt="">
                    <figcaption class="say-panel">
                        <blockquote class="say-bubble">{{ $review['quote'] }}</blockquote>
                        <div class="say-line"></div>
                        <p class="say-name">{{ $review['name'] }}</p>
                        <p class="say-role">{{ $review['role'] }}</p>
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
