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
            background: #fff;
        }
        .say-layout {
            display: grid;
            grid-template-columns: minmax(220px, 300px) minmax(0, 1fr);
            gap: 24px 40px;
            align-items: center;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 72px 0 88px clamp(20px, 4vw, 56px);
        }
        .say-copy {
            min-width: 0;
            padding-left: 0;
        }
        .say-kicker {
            margin: 0 0 10px;
            color: #C0340D;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .say-title {
            margin: 0;
            max-width: 320px;
            color: #152029;
            font-size: clamp(32px, 4vw, 44px);
            line-height: 1.12;
            font-weight: 800;
        }
        .say-lead {
            margin: 14px 0 0;
            max-width: 280px;
            color: #4A5560;
            font-size: 15px;
            line-height: 1.6;
        }
        .say-nav { display: flex; gap: 10px; margin-top: 28px; }
        .say-btn {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border: 1.5px solid #E3E5E8;
            border-radius: 999px;
            background: #fff;
            color: #152029;
            cursor: pointer;
        }
        .say-btn:hover:not(:disabled) { border-color: #FF4917; color: #FF4917; }
        .say-btn:disabled { opacity: 0.35; cursor: default; }
        .say-btn:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .say-stage {
            position: relative;
            width: 100%;
            min-width: 0;
            min-height: 520px;
            overflow: hidden;
        }
        .say-orb {
            position: absolute;
            top: 50%;
            right: -160px;
            z-index: 0;
            width: 620px;
            height: 620px;
            border-radius: 50%;
            background: #FF4917;
            background-image: radial-gradient(circle at 30% 28%, #FF7A45 0%, #FF4917 55%, #D63A10 100%);
            box-shadow: 0 30px 80px rgba(214, 58, 16, 0.3);
            transform: translateY(-50%);
            pointer-events: none;
        }
        .say-orb-glow {
            position: absolute;
            top: 50%;
            right: 40px;
            z-index: 0;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
            transform: translateY(-50%);
            pointer-events: none;
        }
        .say-track {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 18px;
            width: max-content;
            padding: 40px 40px 32px 12px;
            transition: transform 0.4s ease;
        }
        .say-card {
            position: relative;
            z-index: 2;
            width: min(300px, 78vw);
            flex: none;
            margin: 0;
        }
        .say-bubble {
            position: relative;
            margin: 0;
            border-radius: 16px;
            background: #fff;
            padding: 22px 20px 24px;
            color: #4A5560;
            font-size: 14.5px;
            line-height: 1.65;
            box-shadow: 0 14px 34px rgba(21, 32, 41, 0.12);
        }
        .say-bubble::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 26px;
            width: 16px;
            height: 16px;
            background: #fff;
            transform: rotate(45deg);
        }
        .say-person { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
        .say-person img {
            width: 46px;
            height: 46px;
            flex: none;
            border-radius: 999px;
            object-fit: cover;
            box-shadow: 0 0 0 2px #fff;
        }
        .say-name { margin: 0; color: #152029; font-size: 15px; font-weight: 800; }
        .say-role {
            margin: 2px 0 0;
            color: #FF4917;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        @media (max-width: 900px) {
            .say-layout {
                grid-template-columns: 1fr;
                padding: 48px 0 64px;
            }
            .say-copy {
                padding: 0 20px;
            }
            .say-title, .say-lead { max-width: none; }
            .say-stage { min-height: 440px; }
            .say-orb {
                right: -180px;
                width: 460px;
                height: 460px;
            }
            .say-orb-glow {
                right: 20px;
                width: 180px;
                height: 180px;
            }
            .say-track { padding: 32px 20px 24px; }
        }
    </style>
@endonce

<section class="say-section" aria-labelledby="reviews-heading" aria-roledescription="carousel">
    <div class="say-layout">
        <div class="say-copy">
            <p class="say-kicker">Testimonials</p>
            <h2 id="reviews-heading" class="say-title font-display">What our customers are saying</h2>
            <p class="say-lead">Real campaign feedback from teams who order placements through BacklinkPro.</p>
            <div class="say-nav">
                <button type="button" class="say-btn" data-review-prev aria-label="Previous review">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 3 5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="say-btn" data-review-next aria-label="Next review">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
        <div class="say-stage" data-review-stage>
            <div class="say-orb-glow" aria-hidden="true"></div>
            <div class="say-orb" aria-hidden="true"></div>
            <div class="say-track" data-review-track>
                @foreach ($reviews as $review)
                    <figure class="say-card" data-review-card>
                        <blockquote class="say-bubble">{{ $review['quote'] }}</blockquote>
                        <figcaption class="say-person">
                            <img src="{{ asset('images/reviews/'.$review['photo']) }}" alt="" width="46" height="46">
                            <div>
                                <p class="say-name">{{ $review['name'] }}</p>
                                <p class="say-role">{{ $review['role'] }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
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
                    var index = 0;

                    function step() {
                        if (!cards.length) return 0;
                        var styles = window.getComputedStyle(track);
                        var gap = parseFloat(styles.columnGap || styles.gap) || 18;
                        return cards[0].getBoundingClientRect().width + gap;
                    }

                    function maxIndex() {
                        if (!stage || !cards.length) return 0;
                        var visible = Math.max(1, Math.floor((stage.clientWidth + 8) / step()));
                        return Math.max(0, cards.length - visible);
                    }

                    function paint() {
                        if (!track || !cards.length) return;
                        var max = maxIndex();
                        if (index > max) index = max;
                        if (index < 0) index = 0;
                        track.style.transform = 'translateX(' + (-index * step()) + 'px)';
                        if (prev) prev.disabled = index <= 0;
                        if (next) next.disabled = index >= max;
                    }

                    if (next) next.addEventListener('click', function () { index += 1; paint(); });
                    if (prev) prev.addEventListener('click', function () { index -= 1; paint(); });
                    window.addEventListener('resize', paint);
                    paint();
                });
            });
        </script>
    @endpush
@endonce
