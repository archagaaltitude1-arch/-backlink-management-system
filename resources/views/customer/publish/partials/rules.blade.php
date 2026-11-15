<section class="pb-rules" aria-labelledby="rules-title">
    <h2 id="rules-title">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M9 4.5h6v3H9zM9 11h6M9 14h6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
        Publishing rules
    </h2>
    <ul>
        @foreach ($offer['rules'] as $rule)
            <li>{{ $rule }}</li>
        @endforeach
    </ul>
</section>
