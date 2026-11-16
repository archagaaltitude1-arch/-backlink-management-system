<?php

namespace App\Support;

class PublicPackages
{
    /**
     * @return array<int, array{title: string, sections: array<int, array{title: string, items: array<int, array{label: string, slug: string, href: string}>}>}>
     */
    public static function groups(): array
    {
        $levels = [
            ['label' => 'Starter', 'key' => 'starter'],
            ['label' => 'Pro', 'key' => 'pro'],
            ['label' => 'Professional', 'key' => 'professional'],
            ['label' => 'Enterprise', 'key' => 'enterprise'],
        ];

        $services = [
            ['title' => 'Text backlink package', 'slug' => 'text-link'],
            ['title' => 'Contextual backlink package', 'slug' => 'contextual-link'],
            ['title' => 'Guest post package', 'slug' => 'guest-post'],
        ];

        $sections = [];

        foreach ($services as $service) {
            $items = [];

            foreach ($levels as $level) {
                $slug = $level['key'].'-'.$service['slug'];
                $items[] = [
                    'label' => $level['label'],
                    'slug' => $slug,
                    'href' => route('packages.show', $slug),
                ];
            }

            $sections[] = [
                'title' => $service['title'],
                'items' => $items,
            ];
        }

        return [
            [
                'title' => 'Link Building',
                'sections' => $sections,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, slug: string, flag: null, name: string, size: string, summary: string, lookup: array<int, string>}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'Starter Backlink Package',
                'slug' => 'display-starter',
                'flag' => null,
                'name' => 'Starter',
                'size' => '5 backlinks',
                'amount' => 49,
                'price_prefix' => '',
                'popular' => false,
                'summary' => 'A display backlink package for 5 backlinks.',
                'lookup' => ['Starter Backlink Package', 'Starter Package', 'Starter', 'Starter, 5 backlinks'],
            ],
            [
                'label' => 'Growth Backlink Package',
                'slug' => 'display-growth',
                'flag' => null,
                'name' => 'Growth',
                'size' => '10 backlinks',
                'amount' => 89,
                'price_prefix' => '',
                'popular' => true,
                'summary' => 'A display backlink package for 10 backlinks. The price per link is lower than Starter.',
                'lookup' => ['Growth Backlink Package', 'Growth Package', 'Growth', 'Growth, 10 backlinks'],
            ],
            [
                'label' => 'Professional Backlink Package',
                'slug' => 'display-professional',
                'flag' => null,
                'name' => 'Professional',
                'size' => '25 backlinks',
                'amount' => 199,
                'price_prefix' => '',
                'popular' => false,
                'summary' => 'A display backlink package for 25 backlinks, with the lowest price per link.',
                'lookup' => ['Professional Backlink Package', 'Professional Package', 'Professional', 'Professional, 25 backlinks'],
            ],
            [
                'label' => 'Enterprise Backlink Package',
                'slug' => 'display-enterprise',
                'flag' => null,
                'name' => 'Enterprise',
                'size' => 'Custom campaign',
                'amount' => 399,
                'price_prefix' => 'From ',
                'popular' => false,
                'summary' => 'A custom backlink campaign. The price starts here and follows the campaign size.',
                'lookup' => ['Enterprise Backlink Package', 'Enterprise Package', 'Enterprise', 'Enterprise, custom backlink campaign'],
            ],
        ];
    }

    /**
     * @return array{label: string, slug: string, flag: null, name: string, size: string, summary: string, lookup: array<int, string>}|null
     */
    public static function find(string $slug): ?array
    {
        foreach ([...self::items(), ...self::serviceProducts()] as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Customer-facing package choice, such as "Starter – 5 Backlinks".
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $story
     */
    public static function choiceLabel(array $item, array $story): string
    {
        $slug = (string) ($item['slug'] ?? '');
        $level = str_starts_with($slug, 'display-')
            ? (string) ($item['name'] ?? 'Package')
            : ucfirst((string) explode('-', $slug, 2)[0]);
        $count = $story['count'] ?? null;
        $size = is_numeric($count) ? ((int) $count).' Backlinks' : 'Custom Backlinks';

        return $level.' – '.$size;
    }

    /**
     * How many websites this package order needs. Custom packages have no fixed count.
     *
     * @param  array<string, mixed>  $story
     */
    public static function websiteLimit(array $story, int $quantity): ?int
    {
        $count = $story['count'] ?? null;

        if (! is_numeric($count)) {
            return null;
        }

        return (int) $count * max(1, $quantity);
    }

    /**
     * Website chart for a paid package. The first recognized package name wins.
     *
     * @param  iterable<int, mixed>  $names
     * @return array{url: string, label: string}|null
     */
    public static function paidSites(iterable $names): ?array
    {
        foreach ($names as $name) {
            $match = self::matchPackageName((string) $name);

            if ($match === null) {
                continue;
            }

            $query = [];

            if ($match['type'] !== '') {
                $query['type'] = $match['type'];
            }

            if ($match['dr_min'] !== null) {
                $query['dr_min'] = $match['dr_min'];
            }

            $query['package'] = $match['slug'];
            $query['confirmed'] = '1';

            return [
                'url' => route('publishers.index', $query),
                'label' => $match['label'],
            ];
        }

        return null;
    }

    /**
     * @return array{slug: string, label: string, type: string, dr_min: int|null}|null
     */
    private static function matchPackageName(string $name): ?array
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        foreach ([...self::items(), ...self::serviceProducts()] as $item) {
            if (strcasecmp((string) $item['label'], $name) !== 0) {
                continue;
            }

            $offer = self::offerBySlug((string) $item['slug']);
            $type = match ($offer['category'] ?? '') {
                'Text Link' => 'Text link',
                'Contextual Link' => 'Contextual',
                'Guest Post' => 'Guest post',
                default => '',
            };
            $drMin = null;

            if (is_string($offer['dr'] ?? null) && preg_match('/(\d+)/', $offer['dr'], $found) === 1) {
                $drMin = (int) $found[1];
            }

            return [
                'slug' => (string) $item['slug'],
                'label' => (string) $item['label'],
                'type' => $type,
                'dr_min' => $drMin,
            ];
        }

        return null;
    }

    /**
     * Service-page products. These have their own details page and cart line, and they stay off the packages index.
     *
     * @return array<int, array{label: string, slug: string, flag: null, name: string, size: string, summary: string, lookup: array<int, string>}>
     */
    public static function serviceProducts(): array
    {
        return array_map(function (array $offer): array {
            return [
                'label' => $offer['label'],
                'slug' => $offer['slug'],
                'flag' => null,
                'name' => $offer['label'],
                'size' => $offer['size'],
                'amount' => $offer['amount'],
                'price_prefix' => $offer['prefix'],
                'popular' => false,
                'summary' => $offer['lead'],
                'lookup' => [$offer['label']],
            ];
        }, self::offers());
    }

    public static function slugForTier(string $service, string $tier): ?string
    {
        $levels = [
            'text-link' => [
                'Starter' => 'starter',
                'Growth' => 'pro',
                'Professional' => 'professional',
                'Premium' => 'enterprise',
            ],
            'contextual-link' => [
                'Starter' => 'starter',
                'Growth' => 'pro',
                'Professional' => 'professional',
                'Premium' => 'enterprise',
            ],
            'guest-post' => [
                'DR 50+' => 'starter',
                'DR 60+' => 'pro',
                'DR 70+' => 'professional',
            ],
        ];

        $level = $levels[$service][$tier] ?? null;

        return $level === null ? null : $level.'-'.$service;
    }

    /**
     * Product copy for a package details page.
     *
     * @return array<string, mixed>|null
     */
    public static function story(string $slug): ?array
    {
        $offer = self::offerBySlug($slug);

        if ($offer !== null) {
            return self::offerStory($offer);
        }

        $stories = [
            'display-starter' => [
                'headline' => "Build Your Website's SEO Foundation with 5 Quality Backlinks",
                'lead' => 'The Starter Backlink Package is designed for individuals, startups and small businesses looking to begin improving their website\'s search engine visibility through backlink building.',
                'support' => 'Whether you are launching a new website or strengthening your existing online presence, this package provides a simple starting point for building relevant backlinks.',
                'count' => '5',
                'delivery' => '3–5 business days',
                'delivery_note' => 'Suitable for small backlink campaigns.',
                'gets' => [
                    '5 backlinks for your website.',
                    'Relevant website placements based on your selected niche.',
                    'Natural anchor text placement.',
                    'Target URL selection.',
                    'Backlink publication details.',
                    'Order progress tracking.',
                ],
                'audience' => [
                    'New website owners.',
                    'Small businesses.',
                    'Bloggers and content creators.',
                    'Businesses starting their SEO journey.',
                ],
                'steps' => [
                    'Select the Starter Package.',
                    'Add your website URL and target keywords.',
                    'Submit your preferred anchor texts.',
                    'Our SEO team reviews your requirements.',
                    'SEO staff work on your backlink campaign.',
                    'Receive your completed backlink report.',
                ],
            ],
            'display-growth' => [
                'headline' => "Grow Your Website's Online Visibility with 10 Backlinks",
                'lead' => 'The Growth Backlink Package is ideal for businesses looking to expand their backlink profile and strengthen their SEO strategy.',
                'support' => "With 10 backlinks, this package offers more opportunities to build your website's online presence through relevant link placements.",
                'count' => '10',
                'delivery' => '5–7 business days',
                'delivery_note' => 'Allows additional time for link placement and verification.',
                'gets' => [
                    '10 backlinks for your website.',
                    'Niche-relevant backlink opportunities.',
                    'Natural anchor text placement.',
                    'Multiple target URL options.',
                    'Backlink publication details.',
                    'Order progress tracking.',
                ],
                'audience' => [
                    'Growing businesses.',
                    'E-commerce websites.',
                    'Digital marketing agencies.',
                    'Businesses targeting competitive keywords.',
                    'Website owners looking to expand their backlink profile.',
                ],
                'steps' => [
                    'Choose the Growth Package.',
                    'Provide your website and target URLs.',
                    'Submit your anchor texts and niche requirements.',
                    'Our SEO team reviews your order.',
                    'SEO staff complete the backlink activities.',
                    'Receive your backlink report with available publication links.',
                ],
            ],
            'display-professional' => [
                'headline' => 'Strengthen Your SEO Strategy with 25 Backlinks',
                'lead' => 'The Professional Backlink Package is designed for established businesses and website owners who want to expand their backlink-building activities.',
                'support' => "With 25 backlinks, this package supports a broader link-building campaign focused on improving your website's online visibility and developing a stronger backlink profile.",
                'count' => '25',
                'delivery' => '10–15 business days',
                'delivery_note' => 'Suitable for larger backlink campaigns.',
                'gets' => [
                    '25 backlinks for your website.',
                    'Niche-relevant placement opportunities.',
                    'Natural anchor text optimization.',
                    'Multiple target URLs.',
                    'Backlink publication details.',
                    'Campaign progress tracking.',
                    'Final backlink report.',
                ],
                'audience' => [
                    'Established businesses.',
                    'Growing online brands.',
                    'SEO professionals.',
                    'Digital marketing agencies.',
                    'Websites targeting competitive industries.',
                ],
                'steps' => [
                    'Select the Professional Package.',
                    'Submit your website details.',
                    'Provide target URLs and anchor texts.',
                    'Our SEO team plans your backlink campaign.',
                    'SEO staff work on the assigned backlink tasks.',
                    'Receive the completed backlink report.',
                ],
            ],
            'display-enterprise' => [
                'headline' => 'Customized Backlink Campaigns Designed for Your Business',
                'lead' => 'The Enterprise Backlink Package is designed for businesses, agencies and organizations that require a customized backlink-building strategy.',
                'support' => 'Instead of a fixed backlink quantity, this package allows you to discuss your SEO goals, preferred backlink types, target websites and campaign requirements with our team.',
                'count' => 'Custom',
                'delivery' => 'Custom timeline',
                'delivery_note' => 'Delivery schedule will be confirmed after reviewing campaign requirements.',
                'gets' => [
                    'Custom backlink quantity.',
                    'Personalized link-building strategy.',
                    'Niche-specific website targeting.',
                    'Customized anchor text planning.',
                    'Multiple target URL management.',
                    'Campaign progress monitoring.',
                    'Detailed completion report.',
                ],
                'audience' => [
                    'Large businesses.',
                    'SEO agencies managing multiple clients.',
                    'Established brands.',
                    'Businesses targeting competitive markets.',
                    'Organizations requiring customized SEO campaigns.',
                ],
                'steps' => [
                    'Submit an Enterprise campaign request.',
                    'Provide your website and campaign requirements.',
                    'Our team reviews your SEO objectives.',
                    'Receive a customized campaign proposal and quotation.',
                    'Confirm your campaign.',
                    'Our SEO team executes the campaign and provides progress updates.',
                ],
            ],
        ];

        return $stories[$slug] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function offerBySlug(string $slug): ?array
    {
        foreach (self::offers() as $offer) {
            if ($offer['slug'] === $slug) {
                return $offer;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    private static function offerStory(array $offer): array
    {
        return [
            'layout' => 'offer',
            'headline' => $offer['label'],
            'hero' => $offer['label'],
            'badge' => $offer['badge'],
            'category' => $offer['category'],
            'lead' => $offer['lead'],
            'support' => $offer['support'],
            'count' => $offer['count'],
            'delivery' => $offer['delivery'],
            'delivery_note' => $offer['delivery_note'],
            'chips' => $offer['chips'],
            'sample' => $offer['sample'],
            'marks' => ['Technology', 'Finance', 'Business', 'Health'],
            'gets' => $offer['gets'],
            'rules' => $offer['rules'],
            'order_types' => $offer['order_types'],
            'note' => $offer['note'],
            'audience' => [$offer['category'].' buyers'],
            'steps' => [
                'Choose '.$offer['label'].'.',
                'Add the details for this order.',
                'Our team places the order.',
                'We send the live URL after the placement is checked.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function offers(): array
    {
        $linkOrders = [
            [
                'value' => 'placement',
                'label' => 'Text link placement (We will place your link.)',
                'help' => 'We place the link and send the live URL.',
            ],
            [
                'value' => 'anchor-review',
                'label' => 'Anchor review + placement (We will check the anchor, then place the link.)',
                'help' => 'We review the anchor text, then place the link and send the live URL.',
            ],
        ];
        $contextualOrders = [
            [
                'value' => 'placement',
                'label' => 'Contextual placement (We will place your link inside the article.)',
                'help' => 'We place the link in the article body and send the live URL.',
            ],
            $linkOrders[1],
        ];
        $guestOrders = [
            [
                'value' => 'writing',
                'label' => 'Article writing + publication (We will write and publish the article.)',
                'help' => 'Our team writes the article, then publishes it with the links you set.',
            ],
        ];
        $linkNote = 'After checkout, you will be redirected to the requirements page, where you can add the anchor text, target URL, or any other details.';
        $guestNote = 'After checkout, you will be redirected to the requirements page, where you can add the topic, anchor text, target URL, or any other details.';
        $samples = [
            'starter' => 'reports/starter-backlink-sample-report.xlsx',
            'pro' => 'reports/growth-backlink-sample-report.xlsx',
            'professional' => 'reports/professional-backlink-sample-report.xlsx',
            'enterprise' => 'reports/enterprise-backlink-sample-report.xlsx',
        ];

        $text = [
            'starter' => ['Starter', 39, '', '5', '5 links', '5 text links', 'DR 30–40', '2–5 days', false],
            'pro' => ['Pro', 69, '', '10', '10 links', '10 text links', 'DR 40–50', '2–5 days', false],
            'professional' => ['Professional', 149, '', '25', '25 links', '25 text links', 'DR 50–60', '2–5 days', false],
            'enterprise' => ['Enterprise', 199, 'From ', 'Custom', 'Custom', 'Custom text links', 'DR 60+', '2–5 days', true],
        ];
        $contextual = [
            'starter' => ['Starter', 59, '', '5', '5 links', '5 contextual links', 'DR 30–40', '3–7 days', false],
            'pro' => ['Pro', 99, '', '10', '10 links', '10 contextual links', 'DR 40–50', '3–7 days', false],
            'professional' => ['Professional', 219, '', '25', '25 links', '25 contextual links', 'DR 50–60', '3–7 days', false],
            'enterprise' => ['Enterprise', 299, 'From ', 'Custom', 'Custom', 'Custom contextual links', 'DR 60+', '3–7 days', true],
        ];
        $guest = [
            'starter' => ['Starter', 150, '', '10', '10 posts', '10 guest posts', 'DR 50+', 150],
            'pro' => ['Pro', 200, '', '10', '10 posts', '10 guest posts', 'DR 60+', 200],
            'professional' => ['Professional', 400, '', '10', '10 posts', '10 guest posts', 'DR 70+', 400],
            'enterprise' => ['Enterprise', 400, 'From ', 'Custom', 'Custom', 'Custom guest posts', 'DR 70+', 400],
        ];

        $offers = [];

        foreach ($text as $key => $plan) {
            [$level, $amount, $prefix, $count, $badge, $size, $dr, $delivery, $custom] = $plan;
            $countLine = $count === 'Custom' ? 'Custom link count' : $count.' links included';
            $gets = array_values(array_filter([
                $dr.' websites',
                $countLine,
                $custom ? 'High-authority sites' : null,
                $custom ? 'Priority delivery' : null,
                'Link on a relevant existing page',
                'No article writing required',
                'Delivery in '.$delivery,
                'Live URL after the link is checked',
            ]));
            $offers[] = self::offerRow(
                $key.'-text-link',
                $level.' Text Link Package',
                'Text Link',
                $amount,
                $prefix,
                $count,
                $badge,
                $size,
                $dr,
                $delivery,
                $custom
                    ? 'A custom text link campaign on '.$dr.' websites, with priority delivery. The price starts here and follows the campaign size.'
                    : $size.' on '.$dr.' publisher pages. No new article is required, and each link is checked before the live URL is sent.',
                'Use your brand name, a URL, or a keyword as the anchor.',
                $gets,
                [
                    'The link is placed on a relevant existing page, such as a resources section.',
                    'Anchor text can be your brand, a URL, or a keyword, and it should read naturally on the page.',
                    'We do not publish adult content, hate speech, or misleading claims.',
                    'The live URL is sent after the link is checked. Search indexing is not guaranteed.',
                    $custom
                        ? 'The price starts at this amount and follows the campaign size. Turnaround starts after the target URL and anchor text are confirmed.'
                        : 'The '.$delivery.' turnaround starts after the target URL and anchor text are confirmed.',
                ],
                $linkOrders,
                $linkNote,
                $samples[$key],
                [$size, $dr.' websites', $custom ? 'Priority delivery' : $delivery.' delivery', 'Live URL included'],
            );
        }

        foreach ($contextual as $key => $plan) {
            [$level, $amount, $prefix, $count, $badge, $size, $dr, $delivery, $custom] = $plan;
            $countLine = $count === 'Custom' ? 'Custom link count' : $count.' links included';
            $gets = array_values(array_filter([
                $dr.' websites',
                $countLine,
                $custom ? 'High-authority sites' : null,
                $custom ? 'Priority delivery' : null,
                'Link inside the article body',
                'Topic-matched surrounding text',
                'Delivery in '.$delivery,
                'Live URL after verification',
            ]));
            $offers[] = self::offerRow(
                $key.'-contextual-link',
                $level.' Contextual Link Package',
                'Contextual Link',
                $amount,
                $prefix,
                $count,
                $badge,
                $size,
                $dr,
                $delivery,
                $custom
                    ? 'A custom contextual link campaign on '.$dr.' websites, with priority delivery. The price starts here and follows the campaign size.'
                    : $size.' inside topic-matched articles on '.$dr.' websites. The live URL is sent after verification.',
                'The link sits in the article body, surrounded by text that matches your topic. No new article is required.',
                $gets,
                [
                    'The link is placed in the article body, not in a footer, sidebar, or author bio.',
                    'The surrounding text matches the topic of the page you submit.',
                    'You set the anchor text and target URL. Where the publisher allows it, the link can be dofollow.',
                    'We do not publish adult content, hate speech, or misleading claims.',
                    'The live URL is sent after the link is checked. Search indexing is not guaranteed.',
                    $custom
                        ? 'The price starts at this amount and follows the campaign size.'
                        : 'The '.$delivery.' turnaround starts after the target URL and anchor text are confirmed.',
                ],
                $contextualOrders,
                $linkNote,
                $samples[$key],
                [$size, $dr.' websites', $custom ? 'Priority delivery' : $delivery.' delivery', 'Live URL included'],
            );
        }

        foreach ($guest as $key => $plan) {
            [$level, $amount, $prefix, $count, $badge, $size, $dr, $nicheFee] = $plan;
            $custom = $count === 'Custom';
            $posts = $custom ? 'Custom post count' : 'DR '.ltrim($dr, 'DR ').' across '.$count.' websites';
            $gets = [
                $custom ? $dr.' websites' : $posts,
                $custom ? 'Custom post count' : 'Article written and published by our team',
                'Article written and published by our team',
                '2 dofollow contextual links per post',
                'Links on websites with real traffic',
                '600+ word articles (+$100)',
                $custom ? 'Priority delivery' : 'Standard delivery in 2 weeks',
                'Casino and CBD niches (+$'.$nicheFee.')',
            ];
            $offers[] = self::offerRow(
                $key.'-guest-post',
                $level.' Guest Post Package',
                'Guest Post',
                $amount,
                $prefix,
                $count,
                $badge,
                $size,
                $dr,
                '2 weeks',
                $custom
                    ? 'A custom guest post campaign on '.$dr.' websites. The price starts at the '.$dr.' package and follows the number of posts.'
                    : $count.' guest posts on '.$dr.' websites, with 2 dofollow contextual links in each article.',
                'Our team writes and publishes the article. Casino and CBD niches are an extra $'.$nicheFee.'. Articles over 600 words are an extra $100.',
                array_values(array_unique($gets)),
                [
                    'Our team writes and publishes each article.',
                    'Each post includes 2 contextual links. You set the anchor text and target URL.',
                    '600+ word articles are available for an extra $100.',
                    'Casino and CBD niches are available for an extra $'.$nicheFee.'.',
                    'We do not publish adult content, hate speech, or misleading claims.',
                    $custom
                        ? 'The timeline is confirmed after the campaign size is agreed. Search indexing is not guaranteed.'
                        : 'Standard delivery is 2 weeks after the topic, anchor text, and target URL are confirmed. Search indexing is not guaranteed.',
                ],
                $guestOrders,
                $guestNote,
                $samples[$key],
                [$size, $dr.' websites', $custom ? 'Priority delivery' : '2 week delivery', 'Report included'],
            );
        }

        return $offers;
    }

    /**
     * @param  array<int, string>  $gets
     * @param  array<int, string>  $rules
     * @param  array<int, array{value: string, label: string, help: string}>  $orders
     * @param  array<int, string>  $chips
     * @return array<string, mixed>
     */
    private static function offerRow(
        string $slug,
        string $label,
        string $category,
        int $amount,
        string $prefix,
        string $count,
        string $badge,
        string $size,
        string $dr,
        string $delivery,
        string $lead,
        string $support,
        array $gets,
        array $rules,
        array $orders,
        string $note,
        string $sample,
        array $chips,
    ): array {
        return [
            'slug' => $slug,
            'label' => $label,
            'category' => $category,
            'amount' => $amount,
            'prefix' => $prefix,
            'count' => $count,
            'badge' => $badge,
            'size' => $size,
            'dr' => $dr,
            'delivery' => $delivery,
            'delivery_note' => $rules[array_key_last($rules)],
            'lead' => $lead,
            'support' => $support,
            'gets' => $gets,
            'rules' => $rules,
            'order_types' => $orders,
            'note' => $note,
            'sample' => $sample,
            'chips' => $chips,
        ];
    }

    /**
     * Guest-post tiers that already publish a dollar price.
     *
     * @return array<int, array{name: string, price: string, summary: string, features: array<int, string>, popular: bool}>
     */
    public static function guestPostPrices(): array
    {
        $tiers = config('backlink_services.guest-post.tiers', []);
        $priced = [];

        foreach (is_array($tiers) ? $tiers : [] as $tier) {
            if (! is_array($tier)) {
                continue;
            }

            $price = $tier['price'] ?? null;

            if (! is_string($price) || ! str_starts_with($price, '$')) {
                continue;
            }

            $priced[] = [
                'name' => (string) ($tier['name'] ?? ''),
                'price' => $price,
                'summary' => (string) ($tier['summary'] ?? ''),
                'features' => array_values(array_filter($tier['features'] ?? [], 'is_string')),
                'popular' => (bool) ($tier['popular'] ?? false),
            ];
        }

        return $priced;
    }
}
