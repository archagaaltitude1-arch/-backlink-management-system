@php
    $reviews = $offer['reviews'];
    $average = number_format((float) $reviews['average'], 1);
    $count = (int) $reviews['count'];
@endphp
<div role="tabpanel" id="panel-reviews" aria-labelledby="tab-reviews" hidden>
    @if (session('review_status'))
        <p class="pb-flash" role="status">{{ session('review_status') }}</p>
    @endif
    <div class="pb-review-top">
        <div>
            <p class="pb-average">{{ $average }}</p>
            <p class="pb-star-row" role="img" aria-label="Average rating {{ $average }} out of 5">
                <span class="pb-star-base" aria-hidden="true">★★★★★</span>
                <span class="pb-star-fill" style="width: {{ $count > 0 ? min(100, ($reviews['average'] / 5) * 100) : 0 }}%" aria-hidden="true">★★★★★</span>
            </p>
            <p class="pb-review-count">{{ $count }} customer ratings</p>
            <ol class="pb-bars">
                @foreach ([5, 4, 3, 2, 1] as $star)
                    @php
                        $starCount = (int) ($reviews['stars'][$star] ?? 0);
                        $share = $count > 0 ? ($starCount / $count) * 100 : 0;
                    @endphp
                    <li>
                        <span>{{ $star }}★</span>
                        <span class="pb-bar" aria-hidden="true"><span style="width: {{ $share }}%"></span></span>
                        <span>{{ $starCount }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
        <div class="pb-write">
            <h3>Review this publisher</h3>
            <p>Share your experience with other customers.</p>
            @if ($reviewState === 'eligible')
                <button type="button" class="pb-review-btn" data-open-review>Write a review</button>
            @elseif ($reviewState === 'guest')
                <a class="pb-review-btn" href="{{ $loginHref }}">Sign in to review</a>
            @else
                <button type="button" class="pb-review-btn" disabled>You can review after you've ordered from this publisher.</button>
            @endif
        </div>
    </div>

    @if ($reviews['items'] === [])
        <p>There are no reviews yet.</p>
    @else
        <ul class="pb-review-list">
            @foreach ($reviews['items'] as $review)
                <li>
                    <p class="pb-review-name">{{ $review['name'] }} <span class="pb-pill pb-ok">Verified order</span></p>
                    <p class="pb-review-meta" aria-label="{{ $review['rating'] }} out of 5">{{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', 5 - $review['rating']) }} · {{ $review['date'] }}</p>
                    @if ($review['title'] !== '')
                        <h3>{{ $review['title'] }}</h3>
                    @endif
                    <p>{{ $review['comment'] }}</p>
                </li>
            @endforeach
        </ul>
    @endif

    <dialog id="publisher-review-dialog" aria-labelledby="review-dialog-title">
        <form method="POST" action="{{ $reviewAction }}">
            @csrf
            <h3 id="review-dialog-title">Write a review</h3>
            <fieldset>
                <legend>Rating</legend>
                @for ($star = 1; $star <= 5; $star++)
                    <label class="pb-star-choice">
                        <input type="radio" name="rating" value="{{ $star }}" required>
                        {{ $star }} {{ $star === 1 ? 'star' : 'stars' }}
                    </label>
                @endfor
            </fieldset>
            <label class="pb-field">Title
                <input type="text" name="title" maxlength="80" value="{{ old('title') }}">
            </label>
            <label class="pb-field">Comment
                <textarea name="comment" maxlength="1000" required>{{ old('comment') }}</textarea>
            </label>
            <div class="pb-dialog-actions">
                <button type="submit" class="pb-review-btn">Submit</button>
                <button type="button" class="pb-review-btn pb-review-btn-line" data-close-review>Close</button>
            </div>
        </form>
    </dialog>
</div>
