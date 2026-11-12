<?php

namespace App\Support;

class PublicTools
{
    /**
     * @return array<int, array{label: string, slug: string, summary: string, about: string, kind: string, checks: list<string>}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'Website Authority checker',
                'slug' => 'website-authority-checker',
                'summary' => 'Authority score lined up with DR, DA, spam and traffic.',
                'about' => 'Enter a publisher domain to see the authority score (AS) stored for that website, next to Domain Rating, Domain Authority, spam score, traffic and referring domains. The checker returns figures only when the website is already listed.',
                'kind' => 'authority',
                'checks' => ['Authority score (AS)', 'Domain Rating', 'Domain Authority', 'Spam score', 'Monthly traffic', 'Referring domains'],
            ],
            [
                'label' => 'Backlink checker',
                'slug' => 'backlink-checker',
                'summary' => 'Backlinks and referring domains listed for a publisher.',
                'about' => 'Enter a publisher domain to see the backlink count and referring domains stored for that website, plus the link type and how long a placement stays live.',
                'kind' => 'backlinks',
                'checks' => ['Backlinks', 'Referring domains', 'Link type', 'Link validity'],
            ],
            [
                'label' => 'Domain Rating checker',
                'slug' => 'domain-rating-checker',
                'summary' => 'The Domain Rating stored for a publisher website.',
                'about' => 'Domain Rating (DR) is the 0–100 strength figure stored on a publisher listing. Enter a domain to see that DR and the websites it sits beside.',
                'kind' => 'dr',
                'checks' => ['Domain Rating (DR)', 'Domain Authority', 'Referring domains'],
            ],
            [
                'label' => 'Domain Authority checker',
                'slug' => 'domain-authority-checker',
                'summary' => 'The Domain Authority stored for a publisher website.',
                'about' => 'Domain Authority (DA) is the 0–100 figure stored on a publisher listing. Enter a domain to see that DA beside the Domain Rating for the same site.',
                'kind' => 'da',
                'checks' => ['Domain Authority (DA)', 'Domain Rating', 'Authority score'],
            ],
            [
                'label' => 'Traffic checker',
                'slug' => 'traffic-checker',
                'summary' => 'Monthly traffic and top countries for a publisher.',
                'about' => 'Enter a publisher domain to see the monthly traffic figures stored for that website, including the Ahrefs traffic number, the Semrush traffic number and the countries with the largest share.',
                'kind' => 'traffic',
                'checks' => ['Ahrefs traffic', 'Semrush traffic', 'Organic keywords', 'Top countries'],
            ],
            [
                'label' => 'Spam score checker',
                'slug' => 'spam-score-checker',
                'summary' => 'The spam score stored for a publisher website.',
                'about' => 'Enter a publisher domain to see the spam score stored for that website. A lower number means fewer spam signals on the listing. The band is Low (0–30), Medium (31–60) or High (61–100).',
                'kind' => 'spam',
                'checks' => ['Spam score', 'Risk band', 'Domain Rating', 'Domain Authority'],
            ],
            [
                'label' => 'Anchor text analyzer',
                'slug' => 'anchor-text-analyzer',
                'summary' => 'Counts and share for anchors you paste in.',
                'about' => 'Paste one anchor per line. The analyzer counts repeats, shows each anchor’s share of the list, and points out when one phrase takes up 40% or more. It reads only the text you paste. It does not crawl a website.',
                'kind' => 'anchor',
                'checks' => ['Total anchors', 'Unique anchors', 'Share of each phrase', 'A note when one phrase dominates'],
            ],
            [
                'label' => 'Competitor backlink analyzer',
                'slug' => 'competitor-backlink-analyzer',
                'summary' => 'Compare backlinks, DR and DA for two publishers.',
                'about' => 'Enter two publisher domains to compare the backlinks, referring domains, Domain Rating and Domain Authority stored for each one. A side is left blank when that website is not listed.',
                'kind' => 'competitor',
                'checks' => ['Backlinks', 'Referring domains', 'Domain Rating', 'Domain Authority', 'Monthly traffic'],
            ],
        ];
    }

    /**
     * @return array{label: string, slug: string, summary: string, about: string, kind: string, checks: list<string>}|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::items() as $tool) {
            if ($tool['slug'] === $slug) {
                return $tool;
            }
        }

        return null;
    }
}
