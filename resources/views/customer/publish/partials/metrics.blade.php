@php
    $metrics = $offer['metrics'];
    $drSeries = $offer['trends']['dr'] ?? null;
    $trafficSeries = $offer['trends']['traffic'] ?? null;
@endphp
<section class="pb-metrics" aria-label="Website metrics">
    <article class="pb-mbox pb-mbox-authority">
        <div class="pb-mbox-name">Authority</div>
        <div class="pb-mbox-values">
            <div class="pb-mbox-stat" title="Domain Authority: estimated ranking strength, 0–100">
                <span class="sr-only">Domain Authority: estimated ranking strength, 0–100</span>
                <span class="pb-mbox-k">DA</span>
                <strong>{{ $num($metrics['da']) }}</strong>
                <x-ui.sparkline :flat="true" color="#3D7BC4" label="Domain Authority trend, flat preview" />
            </div>
            <div class="pb-mbox-stat" title="Page Authority: estimated page strength, 0–100">
                <span class="sr-only">Page Authority: estimated page strength, 0–100</span>
                <span class="pb-mbox-k">PA</span>
                <strong>{{ $num($metrics['pa']) }}</strong>
            </div>
        </div>
    </article>
    <article class="pb-mbox pb-mbox-trust">
        <div class="pb-mbox-name">Trust</div>
        <div class="pb-mbox-values">
            <div class="pb-mbox-stat" title="Trust Flow: estimated trust from inbound links, 0–100">
                <span class="sr-only">Trust Flow: estimated trust from inbound links, 0–100</span>
                <span class="pb-mbox-k">TF</span>
                <strong>{{ $num($metrics['tf']) }}</strong>
            </div>
            <div class="pb-mbox-stat" title="Citation Flow: estimated link volume, 0–100">
                <span class="sr-only">Citation Flow: estimated link volume, 0–100</span>
                <span class="pb-mbox-k">CF</span>
                <strong>{{ $num($metrics['cf']) }}</strong>
            </div>
        </div>
    </article>
    <article class="pb-mbox pb-mbox-strength pb-mbox-wide">
        <div class="pb-mbox-name">Strength</div>
        <div class="pb-mbox-values">
            <div class="pb-mbox-stat" title="Domain Rating: estimated link strength, 0–100">
                <span class="sr-only">Domain Rating: estimated link strength, 0–100</span>
                <span class="pb-mbox-k">DR</span>
                <strong>{{ $num($metrics['dr']) }}</strong>
                @if (is_array($drSeries) && count($drSeries) >= 2)
                    <x-ui.sparkline :points="$drSeries" color="#FF4917" label="Domain Rating over 12 months" />
                @endif
            </div>
            <div class="pb-mbox-stat" title="Traffic: estimated monthly organic visitors">
                <span class="sr-only">Traffic: estimated monthly organic visitors</span>
                <span class="pb-mbox-k">Traffic</span>
                <strong>{{ $num($metrics['traffic']) }}</strong>
                @if (is_array($trafficSeries) && count($trafficSeries) >= 2)
                    <x-ui.sparkline :points="$trafficSeries" color="#FF4917" label="Estimated traffic over 12 months" />
                @endif
            </div>
            <div class="pb-mbox-stat" title="Keywords: estimated organic keywords">
                <span class="sr-only">Keywords: estimated organic keywords</span>
                <span class="pb-mbox-k">Keywords</span>
                <strong>{{ $num($metrics['keywords']) }}</strong>
            </div>
        </div>
    </article>
    <article class="pb-mbox pb-mbox-links pb-mbox-wide">
        <div class="pb-mbox-name">Link profile</div>
        <div class="pb-mbox-values">
            <div class="pb-mbox-stat" title="Backlinks: estimated inbound links">
                <span class="sr-only">Backlinks: estimated inbound links</span>
                <span class="pb-mbox-k">Backlinks</span>
                <strong>{{ $num($metrics['backlinks']) }}</strong>
            </div>
            <div class="pb-mbox-stat" title="Referring domains: estimated sites linking here">
                <span class="sr-only">Referring domains: estimated sites linking here</span>
                <span class="pb-mbox-k">Ref. domains</span>
                <strong>{{ $num($metrics['referring_domains']) }}</strong>
            </div>
            <div class="pb-mbox-stat" title="Spam: estimated spam score, 0–100">
                <span class="sr-only">Spam: estimated spam score, 0–100</span>
                <span class="pb-mbox-k">Spam</span>
                <strong class="{{ is_numeric($metrics['spam']) && (float) $metrics['spam'] >= 30 ? 'is-risk' : 'is-safe' }}">{{ is_numeric($metrics['spam']) ? $num($metrics['spam']).'%' : '—' }}</strong>
            </div>
        </div>
    </article>
</section>
