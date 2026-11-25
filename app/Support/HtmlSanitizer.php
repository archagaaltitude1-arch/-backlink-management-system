<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class HtmlSanitizer
{
    /**
     * Tags that may remain, with the attributes each tag may keep.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        'p' => ['style'],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'a' => ['href', 'target', 'rel', 'title'],
        'span' => ['style'],
        'div' => ['style'],
        'h2' => ['style'],
        'h3' => ['style'],
        'h4' => ['style'],
        'blockquote' => [],
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            return '';
        }

        $this->cleanChildren($body);

        $result = '';

        foreach ($body->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function cleanChildren(DOMNode $parent): void
    {
        $children = [];

        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                if (! $child instanceof DOMText) {
                    $parent->removeChild($child);
                }

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'link', 'meta'], true)) {
                $parent->removeChild($child);

                continue;
            }

            if (! isset(self::ALLOWED[$tag])) {
                $this->cleanChildren($child);

                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }

                $parent->removeChild($child);

                continue;
            }

            $this->filterAttributes($child, self::ALLOWED[$tag]);
            $this->cleanChildren($child);
        }
    }

    /**
     * @param  list<string>  $allowed
     */
    private function filterAttributes(DOMElement $element, array $allowed): void
    {
        $remove = [];

        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->name);

            if (! in_array($name, $allowed, true) || str_starts_with($name, 'on')) {
                $remove[] = $attribute->name;

                continue;
            }

            if ($name === 'style') {
                $style = $this->textAlign($attribute->value);

                if ($style === null) {
                    $remove[] = $attribute->name;
                } else {
                    $element->setAttribute('style', $style);
                }
            }

            if ($name === 'href') {
                $href = $this->safeHref($attribute->value);

                if ($href === null) {
                    $remove[] = 'href';
                } else {
                    $element->setAttribute('href', $href);
                }
            }
        }

        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }

        if (strtolower($element->tagName) !== 'a') {
            return;
        }

        $target = strtolower($element->getAttribute('target'));

        if (! in_array($target, ['_self', '_blank'], true)) {
            $element->removeAttribute('target');
            $element->removeAttribute('rel');

            return;
        }

        $element->setAttribute('target', $target);

        if ($target === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        } else {
            $element->removeAttribute('rel');
        }
    }

    private function textAlign(string $style): ?string
    {
        if (preg_match('/text-align\s*:\s*(left|right|center|justify)/i', $style, $matches) !== 1) {
            return null;
        }

        return 'text-align: '.strtolower($matches[1]);
    }

    private function safeHref(string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($href === '' || preg_match('/[\x00-\x1F\x7F]/', $href) === 1) {
            return null;
        }

        if (str_starts_with($href, '/') && ! str_starts_with($href, '//') && ! str_starts_with($href, '/\\')) {
            return $href;
        }

        if (preg_match('#^mailto:[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}$#i', $href) === 1) {
            return $href;
        }

        $parts = parse_url($href);

        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return null;
        }

        return $href;
    }
}
