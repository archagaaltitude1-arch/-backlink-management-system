<?php

namespace App\Support;

use App\Rules\MaxKeywords;

class CheckoutItemRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function forItems(): array
    {
        return [
            'items.*.target_url' => ['required', 'url', 'max:2048'],
            'items.*.anchor_text' => ['required', 'string', 'min:15', 'max:20'],
            'items.*.article_topic' => ['required', 'string', 'max:255'],
            'items.*.keywords' => ['nullable', 'string', 'max:500', new MaxKeywords(5)],
            'items.*.requirements' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
