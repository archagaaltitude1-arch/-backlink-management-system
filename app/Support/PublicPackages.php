<?php

namespace App\Support;

class PublicPackages
{
    /**
     * @return array<int, array{title: string, sections: array<int, array{title: string|null, items: array<int, array{label: string, slug: string, flag: string|null, name: string, size: string, summary: string, lookup: array<int, string>}>}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'Link Building',
                'sections' => [
                    [
                        'title' => 'Display backlink packages',
                        'items' => self::items(),
                    ],
                ],
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
        foreach (self::items() as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Product copy for a package details page.
     *
     * @return array<string, mixed>|null
     */
    public static function story(string $slug): ?array
    {
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
