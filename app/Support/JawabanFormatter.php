<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Membersihkan jawaban siswa (sering berupa HTML hasil tempel dari web/Word)
 * menjadi HTML sederhana yang aman dan rapi: tanpa atribut, tanpa style inline,
 * dan strukturnya dibangun ulang sehingga tidak merusak tata letak halaman.
 */
class JawabanFormatter
{
    private const DROP = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select',
        'textarea', 'head', 'title', 'meta', 'link', 'svg', 'canvas', 'noscript', 'img',
        'video', 'audio', 'caption', 'colgroup', 'col',
    ];

    private const BLOCKS = [
        'div' => 'div', 'section' => 'div', 'article' => 'div', 'pre' => 'div', 'p' => 'p',
        'blockquote' => 'blockquote', 'h1' => 'div', 'h2' => 'div', 'h3' => 'div',
        'h4' => 'div', 'h5' => 'div', 'h6' => 'div',
    ];

    private const INLINES = ['b' => 'strong', 'strong' => 'strong', 'i' => 'em', 'em' => 'em', 'u' => 'u'];

    public static function render(?string $raw): string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return '';
        }

        if (!preg_match('/<\/?[a-z][^>]*>/i', $raw)) {
            return '<div class="jw-plain">' . e($raw) . '</div>';
        }

        $previous = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $loaded = $doc->loadHTML(
            '<html><head><meta charset="utf-8"></head><body>' . $raw . '</body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_PARSEHUGE
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $loaded ? $doc->getElementsByTagName('body')->item(0) : null;

        if (!$body) {
            return '<div class="jw-plain">' . e(strip_tags($raw)) . '</div>';
        }

        $html = self::cleanup(self::children($body, 'block'));

        return self::blank($html) ? '' : $html;
    }

    private static function children(DOMNode $node, string $ctx): string
    {
        $out = '';

        foreach ($node->childNodes as $child) {
            $out .= self::node($child, $ctx);
        }

        return $out;
    }

    private static function node(DOMNode $n, string $ctx): string
    {
        if ($n instanceof DOMText) {
            $text = preg_replace('/[\s\x{00A0}]+/u', ' ', (string) $n->nodeValue);
            $structural = in_array($ctx, ['list', 'table', 'tsection', 'row'], true);

            return ($structural && trim($text) === '') ? '' : e($text);
        }

        if (!$n instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($n->tagName);

        if (in_array($tag, self::DROP, true)) {
            return '';
        }

        if ($tag === 'br') {
            return '<br>';
        }

        if (isset(self::INLINES[$tag])) {
            $content = self::children($n, $ctx);
            $mapped = self::INLINES[$tag];

            return self::blank($content) ? $content : "<{$mapped}>{$content}</{$mapped}>";
        }

        if ($tag === 'ul' || $tag === 'ol') {
            $content = self::children($n, 'list');

            return self::blank($content) ? '' : "<{$tag}>{$content}</{$tag}>";
        }

        if ($tag === 'li') {
            $content = trim(self::children($n, 'item'));

            if (self::blank($content)) {
                return '';
            }

            return $ctx === 'list'
                ? "<li>{$content}</li>"
                : "<div class=\"jw-line\">{$content}</div>";
        }

        if (isset(self::BLOCKS[$tag])) {
            if ($ctx === 'list') {
                return self::children($n, 'list');
            }

            $content = trim(self::children($n, $ctx));

            if (self::blank($content)) {
                return '';
            }

            $mapped = self::BLOCKS[$tag];
            $class = $tag[0] === 'h' && strlen($tag) === 2 ? ' class="jw-heading"' : '';

            return "<{$mapped}{$class}>{$content}</{$mapped}>";
        }

        if ($tag === 'table') {
            $content = self::children($n, 'table');

            return self::blank($content) ? '' : "<table>{$content}</table>";
        }

        if (in_array($tag, ['thead', 'tbody', 'tfoot'], true)) {
            if ($ctx !== 'table') {
                return self::children($n, $ctx);
            }

            return "<{$tag}>" . self::children($n, 'tsection') . "</{$tag}>";
        }

        if ($tag === 'tr') {
            if (!in_array($ctx, ['table', 'tsection'], true)) {
                return self::children($n, $ctx);
            }

            return '<tr>' . self::children($n, 'row') . '</tr>';
        }

        if ($tag === 'td' || $tag === 'th') {
            if ($ctx !== 'row') {
                return self::children($n, $ctx);
            }

            $attrs = '';

            foreach (['colspan', 'rowspan'] as $attr) {
                $value = (int) $n->getAttribute($attr);

                if ($value > 1 && $value <= 20) {
                    $attrs .= " {$attr}=\"{$value}\"";
                }
            }

            return "<{$tag}{$attrs}>" . trim(self::children($n, 'cell')) . "</{$tag}>";
        }

        // span, font, a, small, dll: buang pembungkusnya, pertahankan isinya.
        return self::children($n, $ctx);
    }

    private static function cleanup(string $html): string
    {
        $html = preg_replace('/(<br>\s*){3,}/', '<br><br>', $html);
        $open = '(<(?:div|p|li|td|th|strong|em|u|blockquote)(?: class="[^"]*")?>)';
        $html = preg_replace('/' . $open . '\s*(?:<br>\s*)+/', '$1', $html);
        $html = preg_replace('/(?:\s*<br>)+\s*(<\/(?:div|p|li|td|th|strong|em|u|blockquote)>)/', '$1', $html);
        $html = preg_replace('/^(?:\s*<br>)+|(?:<br>\s*)+$/', '', $html);

        return trim($html);
    }

    private static function blank(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace("\xC2\xA0", ' ', $text)) === '';
    }
}
