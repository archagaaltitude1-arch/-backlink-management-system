@php
    $siteOptions = $siteOptions ?? \App\Support\PublicSites::items();
    $languages = [];
    $countries = [];

    foreach ($rows as $row) {
        $language = trim((string) ($row['language'] ?? ''));
        $country = strtoupper((string) ($row['top_countries'][0][0] ?? ''));

        if ($language !== '') {
            $languages[$language] = $language;
        }
        if ($country !== '') {
            $countries[$country] = $country;
        }
    }

    ksort($languages);
    ksort($countries);
    $total = count($rows);
    $defaultType = $defaultType ?? '';
    $defaultNiche = $defaultNiche ?? '';
    $defaultDr = $defaultDr ?? '';
    $defaultCountry = $defaultCountry ?? '';
    $breadcrumb = $breadcrumb ?? 'Publisher list';
    $title = $title ?? 'List of posting websites (All publishers)';
    $lead = $lead ?? 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.';
    $skipIntro = (bool) ($skipIntro ?? false);
    $showRecent = (bool) ($showRecent ?? true);
    $sectionId = $sectionId ?? null;
    $embeddedTitle = $embeddedTitle ?? '';
    $embeddedLead = $embeddedLead ?? '';
    $insertionPrice = (bool) ($insertionPrice ?? false);
    $currentSlug = $currentSlug ?? null;
    $paidLabel = $paidLabel ?? '';
@endphp

<style>
    .pub-wrap { max-width: none; margin: 0 auto; padding: 28px 20px 72px; color: #152029; }
    .pub-crumb { margin: 0 0 18px; color: #4A5560; font-size: 14px; font-weight: 700; }
    .pub-crumb a { color: inherit; text-decoration: none; }
    .pub-crumb a:hover { color: #C0340D; }
    .pub-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .pub-head h1 { margin: 0; max-width: 820px; font-family: Montserrat, sans-serif; font-size: 34px; font-weight: 800; line-height: 1.15; }
    .pub-recent, .pub-create, .pub-apply { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 8px; padding: 0 16px; font-size: 13px; font-weight: 800; letter-spacing: 0.03em; text-decoration: none; text-transform: uppercase; }
    .pub-recent { border: 1px solid #FF4917; background: #fff; color: #FF4917; }
    .pub-recent:hover, .pub-apply:hover { background: #FFF1EC; color: #C0340D; }
    .pub-create, .pub-apply { border: 0; background: #D63A10; color: #fff; }
    .pub-create:hover { background: #FF4917; color: #fff; }
    .pub-lead { max-width: 860px; margin: 14px 0 0; font-size: 16px; line-height: 1.6; }
    .pub-note { margin: 12px 0 0; color: #C0340D; font-size: 14px; font-weight: 700; }
    .pub-status { margin: 0 0 16px; border-radius: 12px; background: #FFF1EC; padding: 12px 14px; color: #C0340D; font-size: 14px; font-weight: 700; line-height: 1.5; }
    .pub-check { margin-top: 22px; }
    .pub-check h2, .pub-project h2 { margin: 0; font-family: Montserrat, sans-serif; font-size: 16px; font-weight: 800; }
    .pub-options { display: grid; gap: 8px 24px; margin: 12px 0 0; padding: 0; list-style: none; }
    .pub-options a { color: #C0340D; font-weight: 700; text-decoration: none; }
    .pub-options a:hover { text-decoration: underline; }
    .pub-project { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 22px; border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 16px; }
    .pub-project p { margin: 4px 0 0; color: #4A5560; }
    .pub-panel { margin-top: 12px; border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 16px; }
    .pub-filters, .pub-tools { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .pub-filters { margin-top: 16px; }
    .pub-filters select, .pub-tools select, .pub-tools input, .pub-panel input { min-height: 44px; border: 1px solid #E3E5E8; border-radius: 10px; background: #fff; color: #152029; font: inherit; font-size: 14px; font-weight: 700; }
    .pub-filters select, .pub-tools select { padding: 0 28px 0 12px; }
    .pub-tools { margin-top: 14px; justify-content: space-between; }
    .pub-tools input { width: min(100%, 320px); padding: 0 12px; font-weight: 500; }
    .pub-count { margin: 0; font-weight: 800; }
    .pub-table { margin-top: 14px; }
    .pub-empty { margin: 0; padding: 18px; color: #4A5560; }
    .pub-embed-head { margin: 8px 0 4px; text-align: center; }
    .pub-kicker { margin: 0 0 12px; color: #C0340D; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; }
    .pub-embed-head h2 { margin: 0; font-family: Montserrat, sans-serif; font-size: clamp(28px, 3vw, 40px); font-weight: 800; line-height: 1.12; }
    .pub-embed-head p:last-child { max-width: 46rem; margin: 16px auto 0; color: #4A5560; font-size: 17px; line-height: 1.65; }
    .pub-wrap :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    @media (min-width: 800px) {
        .pub-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pub-head h1 { font-size: 40px; }
    }
    @media (min-width: 1100px) {
        .pub-options { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
</style>

<section @if ($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-24 bg-[#F7F8FA]" @if ($embeddedTitle !== '') aria-labelledby="publishers-heading" @endif>
    <div class="pub-wrap">
        @if ($paidLabel !== '')
            <p class="pub-status" role="status">Payment confirmed. These websites match your {{ $paidLabel }}.</p>
        @endif
        @unless ($skipIntro)
            <nav class="pub-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $breadcrumb }}</span>
            </nav>

            <div class="pub-head">
                <h1>{{ $title }}</h1>
                <button class="pub-recent" type="button" id="recent-publishers">See recently added sites</button>
            </div>
            <p class="pub-lead">{{ $lead }}</p>
            @if ($demo)
                <p class="pub-note">Sample data for preview. These figures belong to fictional .example publishers.</p>
            @elseif (collect($rows)->contains(fn (array $row): bool => ! empty($row['sample_metrics'])))
                <p class="pub-note">Language, country, keywords, Semrush, referring domains and link details are sample figures until they are stored on each website.</p>
            @endif

            @include('customer.sites.partials.also-check', [
                'siteOptions' => $siteOptions,
                'currentSlug' => $currentSlug,
            ])
        @else
            @if ($embeddedTitle !== '')
                <div class="pub-embed-head">
                    <p class="pub-kicker">Websites</p>
                    <h2 id="publishers-heading">{{ $embeddedTitle }}</h2>
                    @if ($embeddedLead !== '')
                        <p>{{ $embeddedLead }}</p>
                    @endif
                </div>
            @endif
            @if ($showRecent)
                <div class="pub-head" style="justify-content: flex-end;">
                    <button class="pub-recent" type="button" id="recent-publishers">See recently added sites</button>
                </div>
            @endif
        @endunless

        <div class="pub-project">
            <div>
                <h2>Create project</h2>
                <p>Create your first project to organize the websites you choose.</p>
            </div>
            <button class="pub-create" type="button" id="create-project" aria-expanded="false" aria-controls="create-project-panel">Create project</button>
        </div>
        <div id="create-project-panel" class="pub-panel" hidden>
            <form id="create-project-form" class="pub-tools">
                <label class="min-w-0 flex-1">
                    <span class="sr-only">Project name</span>
                    <input id="project-name" name="project_name" type="text" required maxlength="80" placeholder="Project name">
                </label>
                <button class="pub-apply" type="submit">Save project</button>
            </form>
            <p id="create-project-status" class="pub-note" role="status"></p>
        </div>

        @if ($total === 0)
            <p class="pub-empty">No publisher is listed yet.</p>
        @else
            <div class="pub-filters" aria-label="Publisher filters">
                @include('customer.sites.partials.spec-filters', [
                    'languages' => $languages,
                    'countries' => $countries,
                    'categories' => $categories,
                    'defaultNiche' => $defaultNiche,
                    'defaultType' => $defaultType,
                    'defaultDr' => $defaultDr,
                    'defaultCountry' => $defaultCountry,
                ])
            </div>

            <div class="pub-tools">
                <p class="pub-count" id="publisher-count" aria-live="polite">Showing {{ $total }} of {{ $total }} publishers</p>
                <input id="publisher-search" type="search" placeholder="Search by website or niche" aria-label="Search by website or niche">
                <select id="publisher-page-size" aria-label="Rows per page">
                    <option value="10">10 per page</option>
                    <option value="25" selected>25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <select id="publisher-sort" aria-label="Sort publishers">
                    <option value="traffic">Sort by traffic</option>
                    <option value="dr">Sort by DR</option>
                    <option value="da">Sort by DA</option>
                    <option value="price">Sort by price</option>
                    <option value="added">Sort by recently added</option>
                </select>
            </div>

            <div class="pub-table" id="all-publishers">
                @include('customer.sites.table', ['rows' => $rows, 'demo' => $demo, 'filterable' => true, 'insertionPrice' => $insertionPrice])
                <p class="pub-empty" id="publisher-empty" hidden>No publisher matches these filters.</p>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
(function () {
    var root = document.getElementById('all-publishers');
    if (!root) return;
    var rows = Array.prototype.slice.call(root.querySelectorAll('[data-publisher-row]'));
    var count = document.getElementById('publisher-count');
    var empty = document.getElementById('publisher-empty');
    var search = document.getElementById('publisher-search');
    var sort = document.getElementById('publisher-sort');
    var pageSize = document.getElementById('publisher-page-size');
    var page = 1;

    var number = function (row, name) { return Number(row.getAttribute(name) || 0); };
    var selected = function (name) {
        var field = document.querySelector('[data-filter="' + name + '"]');
        return field ? field.value : '';
    };
    var matches = function (row) {
        var query = (search.value || '').trim().toLowerCase();
        if (query && (row.getAttribute('data-search') || '').indexOf(query) === -1) return false;
        var minimums = ['as', 'da', 'dr', 'pa', 'tf', 'traffic', 'semrush', 'keywords', 'backlinks', 'referring'];
        for (var i = 0; i < minimums.length; i++) {
            var min = selected(minimums[i]);
            if (min !== '' && number(row, 'data-' + minimums[i]) < Number(min)) return false;
        }
        var maxPrice = selected('price');
        if (maxPrice !== '' && number(row, 'data-price') > Number(maxPrice)) return false;
        var spamMax = selected('spam');
        if (spamMax !== '' && number(row, 'data-spam') > Number(spamMax)) return false;
        var exact = ['language', 'country', 'type', 'validity', 'sports', 'pharmacy', 'crypto', 'news', 'sponsored', 'foreign'];
        for (var j = 0; j < exact.length; j++) {
            var value = selected(exact[j]);
            if (value !== '' && row.getAttribute('data-' + exact[j]) !== value) return false;
        }
        var niche = selected('niche');
        if (niche !== '' && ('|' + (row.getAttribute('data-niche') || '') + '|').indexOf('|' + niche + '|') === -1) return false;
        return true;
    };
    var paint = function () {
        var visible = rows.filter(matches);
        var key = sort.value;
        visible.sort(function (left, right) {
            return number(right, 'data-' + key) - number(left, 'data-' + key);
        });
        var per = Number(pageSize.value) || 25;
        var pages = Math.max(1, Math.ceil(visible.length / per));
        if (page > pages) page = pages;
        var start = (page - 1) * per;
        var shown = visible.slice(start, start + per);
        rows.forEach(function (row) { row.hidden = true; });
        shown.forEach(function (row) { row.hidden = false; });
        var from = visible.length === 0 ? 0 : start + 1;
        var to = start + shown.length;
        count.textContent = 'Showing ' + from + '–' + to + ' of ' + visible.length + ' publishers';
        empty.hidden = visible.length !== 0;
    };

    document.querySelectorAll('[data-filter]').forEach(function (field) {
        field.addEventListener('change', function () { page = 1; paint(); });
    });
    search.addEventListener('input', function () { page = 1; paint(); });
    sort.addEventListener('change', function () { page = 1; paint(); });
    pageSize.addEventListener('change', function () { page = 1; paint(); });
    var recent = document.getElementById('recent-publishers');
    if (recent) {
        recent.addEventListener('click', function () {
            sort.value = 'added';
            page = 1;
            paint();
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    paint();
})();

(function () {
    var button = document.getElementById('create-project');
    var panel = document.getElementById('create-project-panel');
    var form = document.getElementById('create-project-form');
    var status = document.getElementById('create-project-status');
    if (!button || !panel) return;
    button.addEventListener('click', function () {
        var open = panel.hasAttribute('hidden');
        if (open) panel.removeAttribute('hidden');
        else panel.setAttribute('hidden', '');
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) document.getElementById('project-name').focus();
    });
    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var name = document.getElementById('project-name').value.trim();
            status.textContent = name + ' is ready on this page. Choose publishers from the list below.';
        });
    }
})();
</script>
@endpush
