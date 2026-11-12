<?php

/*
|--------------------------------------------------------------------------
| BacklinkPro – Service landing page content (Member 2 / Sanukshika)
|--------------------------------------------------------------------------
| Edit text here; the page template (customer/services/show.blade.php)
| reads everything from this file. Replace [N] and [PRICE] before launch.
*/

$niches = [
    'Technology', 'Software & IT', 'Business', 'Finance', 'Health',
    'Education', 'Travel', 'Fashion & Beauty', 'Lifestyle', 'Real Estate',
    'E-commerce', 'Automotive', 'Sports', 'Food', 'Agriculture & Farming',
];

$tiers = function (array $features, string $premiumNote) {
    return [
        ['name' => 'Starter',      'dr_label' => 'DR 30–40', 'dr_min' => 30, 'links' => '5',      'price' => '[PRICE]', 'popular' => false, 'features' => $features],
        ['name' => 'Growth',       'dr_label' => 'DR 40–50', 'dr_min' => 40, 'links' => '10',     'price' => '[PRICE]', 'popular' => true,  'features' => $features],
        ['name' => 'Professional', 'dr_label' => 'DR 50–60', 'dr_min' => 50, 'links' => '25',     'price' => '[PRICE]', 'popular' => false, 'features' => $features],
        ['name' => 'Premium',      'dr_label' => 'DR 60+',   'dr_min' => 60, 'links' => 'Custom', 'price' => '[PRICE]', 'popular' => false, 'features' => [$premiumNote, 'Priority delivery', 'Verified report']],
    ];
};

return [

    /* ---------------- Services menu + overview page ---------------- */
    'menu' => [
        ['slug' => 'guest-post',      'name' => 'Guest Post',      'line' => 'New articles on relevant blogs and news sites'],
        ['slug' => 'niche-edit',      'name' => 'Niche Edit',      'line' => 'Your link inside aged, indexed articles'],
        ['slug' => 'contextual-link', 'name' => 'Contextual Link', 'line' => 'In-content links surrounded by related text'],
        ['slug' => 'text-link',       'name' => 'Text Link',       'line' => 'Simple, low-cost links on relevant pages'],
    ],

    'overview' => [
        'h1'    => 'Link Building Services for Every Goal and Budget',
        'intro' => 'Four ways to earn backlinks on real publisher websites. Each one is placed by our team, checked live and reported back to you with full details.',
        'compare_rows' => [
            //            label            guest-post            niche-edit             contextual-link        text-link
            ['label' => 'Content',       'guest-post' => 'New article',            'niche-edit' => 'Existing article',      'contextual-link' => 'Existing content',     'text-link' => 'None needed'],
            ['label' => 'Link location', 'guest-post' => 'Inside the new article', 'niche-edit' => 'Inside an aged article', 'contextual-link' => 'Inside article text',  'text-link' => 'Relevant page or resource list'],
            ['label' => 'Who writes',    'guest-post' => 'Our team or you',        'niche-edit' => 'Already published',     'contextual-link' => 'Already published',    'text-link' => 'None'],
            ['label' => 'Anchor text',   'guest-post' => 'Your choice',            'niche-edit' => 'Your choice',           'contextual-link' => 'Your choice',          'text-link' => 'Brand, URL or keyword'],
            ['label' => 'Turnaround',    'guest-post' => '5–14 days',              'niche-edit' => '3–7 days',              'contextual-link' => '3–7 days',             'text-link' => '2–5 days'],
            ['label' => 'Best for',      'guest-post' => 'Topical authority',      'niche-edit' => 'Faster results',        'contextual-link' => 'Strong relevance',     'text-link' => 'Low-cost authority'],
            ['label' => 'Price from',    'guest-post' => '[PRICE]',                'niche-edit' => '[PRICE]',               'contextual-link' => '[PRICE]',              'text-link' => '[PRICE]'],
        ],
    ],

    /* ---------------- Shared blocks ---------------- */
    'shared' => [
        'trust' => ['[N]+ Checked Publishers', 'White-Hat Placements Only', 'Every Link Verified'],
        'reviews_title' => 'What Our Customers Say',
        'niches' => $niches,
        'areas' => [
            ['icon' => 'layers', 'title' => 'Technology', 'text' => 'Software, gadget and SaaS publishers where technical readers already look for answers.'],
            ['icon' => 'chart', 'title' => 'Finance', 'text' => 'Money, markets and business sites read by people comparing products and services.'],
            ['icon' => 'shield', 'title' => 'Health', 'text' => 'Health and wellness publishers that already cover advice, research and everyday care.'],
            ['icon' => 'users', 'title' => 'Business', 'text' => 'Professional publications used by teams, founders and people making buying decisions.'],
            ['icon' => 'globe', 'title' => 'Travel & Lifestyle', 'text' => 'Travel, food, fashion and home sites that reach people planning what to buy next.'],
            ['icon' => 'pen', 'title' => 'Education', 'text' => 'Learning and career sites where guides stay useful long after they are published.'],
        ],
        'buttons' => [
            'primary'   => 'Publisher Websites & Pricing',
            'secondary' => 'View Packages',
            'start'     => 'Start Your Order',
            'contact'   => 'Talk to Our Team',
        ],
    ],

    /* ---------------- Guest Post ---------------- */
    'guest-post' => [
        'name'         => 'Guest Post',
        'pill'         => 'Guest Posting & Blogger Outreach',
        'turnaround'   => '5–14 Days',
        'h1'           => 'Guest Posting Service on High-Authority Blogs and News Sites',
        'h1_highlight' => 'High-Authority',
        'intro'        => 'Publish brand-new articles on relevant blogs, magazines and news websites, each carrying a dofollow link to your site. Made for businesses, SEO agencies and marketers who want authority that lasts.',
        'tagline'      => 'Content that earns its place, and links that keep working.',
        'why_title'    => 'Why Businesses Choose Our Guest Posting Service',
        'why_intro'    => 'BacklinkPro helps businesses grow with <strong>guest posts on real publisher websites</strong>. Our writers prepare an <strong>original article</strong> for your topic, or you can send your own for review. Each article includes a link to your page and is published on a site that already covers your niche. You choose the publishers that fit, and we handle the placement. Most posts include a <strong>dofollow link</strong> inside the article, and we send the <strong>live URL</strong> only after the post is verified.',
        'why' => [
            ['Real Blogs, Real Audiences', 'Every website is checked for genuine organic traffic and original content before we list it.'],
            ['Written by SEO Writers', 'Our writers create readable, on-topic articles that publishers are happy to accept.'],
            ['Your Article Welcome', 'Already have content? Submit it and our team reviews it before it goes to the publisher.'],
            ['Choose Your Anchor', 'You set the target URL and anchor text, so every link supports your keyword plan.'],
            ['Pay After Verification', 'Credits are reserved when you order and only deducted once your link is confirmed live.'],
            ['Agency-Ready Reports', 'White-label reports with the live URL, anchor and site metrics for every post.'],
        ],
        'areas_title'  => 'Guest Posts Across Every Major Niche',
        'areas_intro'  => 'Publish where your customers already read.',
        'areas_points' => ['New article written for your topic', 'In-content dofollow link', 'Your own article accepted', 'Images and clean formatting'],
        'steps_title'  => 'How Our Guest Posting Works',
        'steps' => [
            ['Choose websites', 'Filter the marketplace by niche, DR, traffic and price.'],
            ['Share your details', 'Add your target URL, anchor text and topic, or upload your own article.'],
            ['We write and review', 'Our team writes or checks the content for quality and relevance.'],
            ['Article goes live', 'The publisher posts the article on the chosen website.'],
            ['Get your report', 'We verify the link and send you the live URL and metrics.'],
        ],
        'packages_title' => 'Guest Post Packages',
        'packages_intro' => 'Choose a package that fits your requirements.',
        'tiers' => [
            [
                'name' => 'DR 50+',
                'dr_label' => 'DR 50+',
                'dr_min' => 50,
                'links' => '10',
                'summary' => '10 guest posts included',
                'price' => '$150',
                'popular' => false,
                'icon' => 'layers',
                'features' => [
                    'DR 50+ across 10 websites',
                    'Publish your articles',
                    '2 dofollow contextual links per post',
                    'Links on websites with real traffic',
                    '600+ word articles (+$100)',
                    'Standard delivery in 2 weeks',
                    'Casino and CBD niches (+$150)',
                ],
            ],
            [
                'name' => 'DR 60+',
                'dr_label' => 'DR 60+',
                'dr_min' => 60,
                'links' => '10',
                'summary' => '10 guest posts included',
                'price' => '$200',
                'popular' => false,
                'icon' => 'chart',
                'features' => [
                    'DR 60+ across 10 websites',
                    'Publish your articles',
                    '2 dofollow contextual links per post',
                    'Links on websites with real traffic',
                    '600+ word articles (+$100)',
                    'Standard delivery in 2 weeks',
                    'Casino and CBD niches (+$200)',
                ],
            ],
            [
                'name' => 'DR 70+',
                'dr_label' => 'DR 70+',
                'dr_min' => 70,
                'links' => '10',
                'summary' => '10 guest posts included',
                'price' => '$400',
                'popular' => true,
                'icon' => 'shield',
                'features' => [
                    'DR 70+ across 10 websites',
                    'Publish your articles',
                    '2 dofollow contextual links per post',
                    'Links on websites with real traffic',
                    '600+ word articles (+$100)',
                    'Standard delivery in 2 weeks',
                    'Casino and CBD niches (+$400)',
                ],
            ],
        ],
        'reviews' => [
            ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'Our guest post went live on a relevant blog, and the report included the live URL after the link was checked.'],
            ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'We sent our own article. The team reviewed it, and the publisher posted it with the anchor we chose.'],
            ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'The new article matched our topic, and the dofollow link sat inside the post rather than in a footer.'],
            ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'Choosing the websites first made the order clear. The post was live inside the stated delivery window.'],
            ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'The writers handled the draft. We only confirmed the target URL and anchor before it went to the publisher.'],
            ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'The white-label report was easy to forward, with the live URL and the publisher site we had picked.'],
        ],
        'faqs' => [
            ['Do you write the article?', 'Yes. Our writers create an original article on your topic, or you can submit your own for review.'],
            ['Are the links dofollow?', 'Each website shows its link type before you buy; most guest posts include a dofollow in-content link.'],
            ['How long does it take?', 'Usually 5 to 14 days, depending on the publisher.'],
            ['Can I choose the anchor text?', 'Yes. You set the anchor text and target URL when you order.'],
            ['What if the post is rejected?', 'Your reserved credits are released back to your wallet.'],
        ],
        'cta_title' => 'Ready to Publish Your First Guest Post?',
        'cta_text'  => "Pick a website, share your topic and we'll handle the rest.",
        'meta'      => 'Guest posting service on real blogs and news sites with dofollow links, niche relevance and verified reports.',
    ],

    /* ---------------- Niche Edit ---------------- */
    'niche-edit' => [
        'name'         => 'Niche Edit',
        'pill'         => 'Link Insertions in Aged Content',
        'turnaround'   => '3–7 Days',
        'h1'           => 'Niche Edit Service: Links Inside Articles That Already Rank',
        'h1_highlight' => 'Already Rank',
        'intro'        => 'We add your link to existing, indexed articles that already have age, traffic and authority. It\'s a quicker route to results than waiting for new content to mature.',
        'tagline'      => 'Put your link where readers already are.',
        'why_title'    => 'Why Marketers Choose Our Niche Edits',
        'why_intro'    => 'BacklinkPro adds your link to <strong>articles that are already published and indexed</strong>. There is no wait for a new post to mature, because the page already has readers. We adjust a sentence so the link fits the original article, and we check that the topic matches your page first. You choose the website, and we suggest a relevant article. Credits are used only after the <strong>link is confirmed live</strong>, and your report includes the <strong>live URL</strong>.',
        'why' => [
            ['Pages With History', 'Your link lands on articles that search engines have already crawled and trust.'],
            ['Faster Impact', 'No waiting for a new post to be indexed; the page is live and earning traffic today.'],
            ['Natural Placement', 'We adjust a sentence so your link reads as part of the original article.'],
            ['Topic Match Checked', 'Our team confirms the article matches your page before the link is added.'],
            ['Pay After Verification', 'Credits are only deducted once your link is confirmed live.'],
            ['Clear Reporting', 'See the article URL, anchor, page age and site metrics in your report.'],
        ],
        'areas_title'  => 'Niche Edits on Relevant, Aged Pages',
        'areas_intro'  => 'Choose existing articles that match your topic and audience.',
        'areas_points' => ['Existing indexed article', 'Link added in a natural sentence', 'No new content needed', 'Page age and traffic shown'],
        'steps_title'  => 'How Our Niche Edits Work',
        'steps' => [
            ['Pick a website', 'Filter by niche, DR and traffic.'],
            ['Send your URL and anchor', 'Tell us your target page and preferred anchor text.'],
            ['We find the best article', 'Our team picks a relevant existing post on that site.'],
            ['Link is inserted', 'The publisher adds your link in context.'],
            ['Verified report', "We confirm it's live and send you the details."],
        ],
        'packages_title' => 'Niche Edit Packages',
        'tiers' => $tiers(['Aged article', 'Dofollow link', 'Verified report'], 'High-traffic aged pages'),
        'reviews' => [
            ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'The link was added to an article that was already indexed, so we did not wait for a new post to age.'],
            ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'They matched our page to an existing article in the same niche before the publisher inserted the link.'],
            ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'The edit read as part of the original article. The live URL arrived once the link was confirmed.'],
            ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'A niche edit was the faster option for us when we did not need a full new article.'],
            ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'We set the anchor and target URL, and the team found a relevant aged page on the site we chose.'],
            ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'The report showed the article URL, the anchor, and that the link was live.'],
        ],
        'faqs' => [
            ['What is a niche edit?', 'A link added to an article that is already published and indexed.'],
            ['Will the article change?', 'Only a sentence or phrase is adjusted so your link fits naturally.'],
            ['How fast is it?', 'Most niche edits go live within 3 to 7 days.'],
            ['Can I pick the article?', 'You choose the website; we suggest the most relevant article and you can ask for alternatives.'],
            ["How do I know it's live?", 'We check every link before reporting and send you the live URL.'],
        ],
        'cta_title' => 'Get Links on Pages That Already Perform',
        'cta_text'  => "Choose a website and we'll place your link in a relevant aged article.",
        'meta'      => 'Niche edit link insertions in aged, indexed articles with real traffic. Fast delivery and verified reports.',
    ],

    /* ---------------- Contextual Link ---------------- */
    'contextual-link' => [
        'name'         => 'Contextual Link',
        'pill'         => 'In-Content Links',
        'turnaround'   => '3–7 Days',
        'h1'           => 'Contextual Links Placed Naturally Inside Relevant Content',
        'h1_highlight' => 'Naturally',
        'intro'        => 'Your link sits in the body of a topic-matched article, surrounded by related words. That is the kind of placement search engines treat as a genuine recommendation.',
        'tagline'      => 'Relevance first, rankings follow.',
        'why_title'    => 'Why Choose Our Contextual Links',
        'why_intro'    => 'BacklinkPro places your link in the <strong>body of a relevant article</strong>, surrounded by words that match your topic. It does not sit in a footer, sidebar, or author bio. You set the anchor text and the page the link should point to, and we choose a publisher that already covers your field. Most placements are a <strong>dofollow link</strong> where the site allows it. We send the <strong>live URL</strong> only after the link is verified.',
        'why' => [
            ['Inside the Main Text', 'Your link appears in the article body, not in a footer, sidebar or author bio.'],
            ['Surrounded by Relevance', 'The words around your link match your topic, which strengthens its value.'],
            ['Real Traffic Sites', "Every publisher is checked for organic visitors before it's listed."],
            ['Your Anchor, Your Target', 'Set the exact anchor text and landing page for each link.'],
            ['Pay After Verification', 'Credits are deducted only after the link is confirmed live.'],
            ['Transparent Reports', 'Live URL, anchor, placement and metrics for every link you buy.'],
        ],
        'areas_title'  => 'Contextual Links by Industry',
        'areas_intro'  => 'Match your link to content written for your field.',
        'areas_points' => ['Link inside the main article text', 'Topic-matched surrounding content', 'Dofollow where the site allows', 'Anchor text of your choice'],
        'steps_title'  => 'How Contextual Links Work',
        'steps' => [
            ['Select websites', 'Use filters for niche, DR, DA and traffic.'],
            ['Add your link details', 'Target URL and anchor text.'],
            ['Placement planned', 'We choose the right content and position for your link.'],
            ['Link goes live', 'Published inside the article body.'],
            ['Verified report', 'Live URL and metrics appear in your dashboard.'],
        ],
        'packages_title' => 'Contextual Link Packages',
        'tiers' => $tiers(['In-content link', 'Niche match', 'Verified report'], 'High-authority sites'),
        'reviews' => [
            ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'The link sits in the body of a related article, surrounded by text that mentions our topic.'],
            ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'A contextual placement felt more natural for this page than a bare link on a resources list.'],
            ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'We could see the niche and the page before ordering, then received the live URL after verification.'],
            ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'A short edit made the link belong in the paragraph. It did not look dropped in.'],
            ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'The dofollow link is inside the article, which is what this campaign needed.'],
            ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'The verified report listed the page, the anchor, and where the link sits in the article.'],
        ],
        'faqs' => [
            ['How is this different from a text link?', 'A contextual link sits inside article text, not on a resource page or sidebar.'],
            ['Do I need to write content?', 'No. Your link is placed in suitable content on the publisher site.'],
            ['How long does it take?', 'Usually 3 to 7 days.'],
            ['Can I target a specific niche?', 'Yes. Filter the marketplace by niche before you order.'],
            ['Do you verify every link?', 'Yes. Each link is checked live before we report it.'],
        ],
        'cta_title' => 'Start Building Contextual Links Today',
        'cta_text'  => 'Browse relevant websites and place your first in-content link.',
        'meta'      => 'Contextual backlinks placed inside relevant article content on real websites, with verified delivery.',
    ],

    /* ---------------- Text Link ---------------- */
    'text-link' => [
        'name'         => 'Text Link',
        'pill'         => 'Simple, Affordable Links',
        'turnaround'   => '2–5 Days',
        'h1'           => 'Text Link Service for Fast, Affordable Authority',
        'h1_highlight' => 'Fast, Affordable',
        'intro'        => "Get a clean link to your website on a relevant page of a real publisher site. It's a cost-effective way to add authority and brand mentions without ordering new content.",
        'tagline'      => 'Quick to order, quick to go live.',
        'why_title'    => 'Why Choose Our Text Links',
        'why_intro'    => 'BacklinkPro adds a <strong>simple text link</strong> on a relevant page of a real publisher website. You do not need a new article, so the placement can go live quickly. Use your brand name, a URL, or a keyword as the anchor, and choose the sites that fit your niche. It is a practical way to add authority without ordering content. We confirm the link and send the <strong>live URL</strong> before the order is complete.',
        'why' => [
            ['Lowest Cost per Link', 'The most budget-friendly way to add links from real, relevant websites.'],
            ['No Writing Needed', "Your link goes on an existing page, so there's no article to wait for."],
            ['Quick Delivery', 'Most text links are live within 2 to 5 days.'],
            ['Good for Brand Signals', 'Use your brand name or URL as anchor to build a natural link profile.'],
            ['Pay After Verification', 'Credits are deducted only after the link is confirmed live.'],
            ['Easy to Scale', 'Add many websites to your cart or use packages for bulk orders.'],
        ],
        'areas_title'  => 'Text Links Across Popular Niches',
        'areas_intro'  => 'Place your link on pages that fit your industry.',
        'areas_points' => ['Link on a relevant existing page', 'No content required', 'Brand, URL or keyword anchors', 'Lowest cost per link'],
        'steps_title'  => 'How Text Links Work',
        'steps' => [
            ['Choose websites', 'Filter by niche, metrics and price.'],
            ['Add URL and anchor', 'Tell us where the link should point.'],
            ['Placement', 'The publisher adds your link to a relevant page.'],
            ['Verified report', 'We confirm the link and send you the live URL.'],
        ],
        'packages_title' => 'Text Link Packages',
        'tiers' => $tiers(['Relevant page', 'Verified report'], 'High-authority sites'),
        'reviews' => [
            ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'A simple text link on a relevant page was enough for this brief, and the live URL came back checked.'],
            ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'We picked the site, sent the URL and anchor, and the publisher added the link without a full article.'],
            ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'Text links kept this order lighter while the page still matched our niche.'],
            ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'Delivery was straightforward, and the report confirmed the link was live on the page we selected.'],
            ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'A brand anchor on a relevant page was simple to order and easy to show a client.'],
            ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'No article writing was needed. We sent the target URL and received a verified text link.'],
        ],
        'faqs' => [
            ['Where does a text link appear?', 'On a relevant existing page of the publisher site, such as a resources section.'],
            ['Is it dofollow?', 'Each website shows its link type before you buy.'],
            ['How fast is delivery?', 'Usually 2 to 5 days.'],
            ['Does it help SEO?', 'Yes, especially for brand signals and adding authority at low cost.'],
            ['Can I order in bulk?', 'Yes. Use packages or add many websites to your cart.'],
        ],
        'cta_title' => 'Add Authority Without the Wait',
        'cta_text'  => 'Order text links on relevant publisher websites in minutes.',
        'meta'      => 'Affordable text link service on real publisher websites. Fast delivery and verified links.',
    ],

];
