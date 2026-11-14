<?php

return [
    'demo' => env('MARKETPLACE_DEMO', false),
    'hide_urls' => env('MARKETPLACE_HIDE_URLS', false),
    'writing_addon_price' => null, // set by team; when null show "[PRICE]" and add nothing to subtotal
    'order_types' => [
        'content_placement' => [
            ['key' => 'publish_only', 'label' => 'Publish my article', 'help' => 'You supply the finished article and we publish it.', 'product_key' => 'guest_post', 'addon' => false],
            ['key' => 'write_and_publish', 'label' => 'Write and publish for me', 'help' => 'Our writers create the article, then we publish it.', 'product_key' => 'guest_post', 'addon' => true],
            ['key' => 'contextual_link', 'label' => 'Contextual link', 'help' => 'Your link placed inside relevant article text.', 'product_key' => 'contextual_link', 'addon' => false],
            ['key' => 'text_link', 'label' => 'Text link', 'help' => 'A link on a relevant existing page.', 'product_key' => 'text_link', 'addon' => false],
        ],
        'link_insertion' => [
            ['key' => 'niche_edit', 'label' => 'Niche edit (link insertion)', 'help' => 'One link added to an existing article, subject to publisher approval.', 'product_key' => 'niche_edit', 'addon' => false],
        ],
    ],
    'content_topics' => ['General', 'Business', 'Technology', 'Health', 'Finance', 'Travel', 'Lifestyle', 'Education', 'Home & Garden', 'Animals & Pets', 'Other'],
];
