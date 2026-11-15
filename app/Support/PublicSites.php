<?php

namespace App\Support;

class PublicSites
{
    /**
     * @return array<int, array{label: string, slug: string, kind: string, about: string, headline: string, detail: string, chart: string|null, checks: list<string>}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'Premium Guest Posting Sites | Top Tier Publishers List',
                'slug' => 'premium-guest-posting-sites',
                'kind' => 'premium-guest',
                'about' => 'These are guest post publishers, ordered by the Domain Rating stored on each listing. The highest stored Domain Rating is first.',
                'headline' => 'Handpicked high-authority websites for guest posting',
                'detail' => 'Each row keeps the niche, traffic, keywords and link type already stored for that website. A higher place means a higher stored Domain Rating. It is not a forecast of rankings. Compare DA, DR and organic traffic on every listing before you buy.',
                'chart' => 'dr',
                'checks' => ['Guest post publishers only', 'Ordered by stored Domain Rating', 'Traffic and keywords from the same listing', 'Niche shown for each website'],
            ],
            [
                'label' => 'Link Insertions (Niche Edits) Sites List',
                'slug' => 'link-insertions-niche-edits',
                'kind' => 'niche-edit',
                'about' => 'Browse every publisher website here, with Niche edit selected by default so you can buy link insertions into aged articles.',
                'headline' => 'Link insertions on aged, indexed articles',
                'detail' => 'Clear or change the Link type filter to see all sites. Use Niche and the other filters to narrow the chart, then open the site you want.',
                'chart' => 'dr',
                'checks' => ['All publishers on one chart', 'Niche edit pre-selected', 'Niches filterable', 'Change filters to see every site'],
            ],
            [
                'label' => 'List of Guest Posting Sites with Organic Traffic',
                'slug' => 'guest-posting-organic-traffic',
                'kind' => 'guest-traffic',
                'about' => 'These are guest post publishers that have a monthly traffic figure stored above zero. The highest stored traffic is first.',
                'headline' => 'Guest posting sites with real organic traffic',
                'detail' => 'A guest post website with no stored traffic is left off this list. Domain Rating and keywords beside each row come from the same publisher listing.',
                'chart' => 'traffic',
                'checks' => ['Guest post publishers only', 'Monthly traffic stored above zero', 'Highest stored traffic first', 'Domain Rating kept beside each row'],
            ],
            [
                'label' => 'Top Traffic Websites by Country',
                'slug' => 'top-traffic-by-country',
                'kind' => 'by-country',
                'about' => 'Publishers are grouped by the country with the largest share of their stored traffic. Each group is a separate country list.',
                'headline' => 'Publisher websites grouped by top traffic country',
                'detail' => 'Inside a country, the website with the highest stored traffic is first. The preview compares that top traffic figure from country to country.',
                'chart' => 'country',
                'checks' => ['Grouped by the top stored country', 'Highest traffic first inside each country', 'Country code taken from the listing', 'Websites with no country kept in their own group'],
            ],
            [
                'label' => 'Websites that accept Sidebar/Footer/Blogroll Backlinks',
                'slug' => 'sidebar-footer-blogroll',
                'kind' => 'sidebar',
                'about' => 'A publisher is included only when its stored link type mentions a sidebar, footer, or blogroll placement.',
                'headline' => 'Sidebar, footer and blogroll placement sites',
                'detail' => 'If no listing uses those words, this page stays empty. It does not guess which websites accept that placement.',
                'chart' => 'dr',
                'checks' => ['Sidebar placements when stored', 'Footer placements when stored', 'Blogroll placements when stored', 'Empty when none of those link types are stored'],
            ],
            [
                'label' => 'Favorite Websites',
                'slug' => 'favorite-websites',
                'kind' => 'favorites',
                'about' => 'Favorite websites are the publishers you save from a listing with the heart control. They stay on your account.',
                'headline' => 'Your saved publisher shortlist',
                'detail' => 'Sign in to open the websites saved for you. This page does not publish a made-up shortlist of favorites.',
                'chart' => null,
                'checks' => ['Saved from a publisher listing', 'Kept on your account', 'Opened after you sign in', 'Not a public ranked list'],
            ],
            [
                'label' => '100+ Organic Keywords Guest Posting Sites',
                'slug' => 'organic-keywords-guest-posts',
                'kind' => 'keywords',
                'about' => 'These are guest post publishers whose stored organic keyword count is 100 or more. The highest keyword count is first.',
                'headline' => 'Guest posts on sites with 100+ organic keywords',
                'detail' => 'The keyword number is the figure stored on the listing. Guest post publishers under 100 keywords are left off, as are publishers that are not guest posts.',
                'chart' => 'keywords',
                'checks' => ['Guest post publishers only', '100 or more stored organic keywords', 'Highest keyword count first', 'Domain Rating and traffic kept beside each row'],
            ],
        ];
    }

    /**
     * @return array{label: string, slug: string, kind: string, about: string, headline: string, detail: string, chart: string|null, checks: list<string>}|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::items() as $site) {
            if ($site['slug'] === $slug) {
                return $site;
            }
        }

        return null;
    }
}
