<?php
declare(strict_types=1);

namespace App\Core;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Panelden girilen zengin metni (HTML) guvenli hale getirir.
 * Yalnizca izinli etiketler ve nitelikler kalir; script, style, olay nitelikleri (onclick vb.),
 * javascript: baglantilari ve diger her sey atilir. Izinsiz etiketlerin metni korunur.
 *
 * Kullanim: $temiz = HtmlSanitizer::clean($_POST['body']);
 * Kayit SIRASINDA cagrilir; sitede icerik oldugu gibi basilir.
 */
final class HtmlSanitizer
{
    /** etiket => izinli nitelikler */
    private const ALLOWED = [
        'p' => [], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [],
    ];

    /** Icerigiyle birlikte tamamen atilan etiketler */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript', 'svg', 'math', 'form', 'textarea', 'select', 'button', 'head', 'title', 'meta', 'link'];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        // UTF-8 bildirimi olmadan DOMDocument metni ISO-8859-1 sanar
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__root');
        if ($root === null) {
            return '';
        }
        self::walk($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }
        // Bos paragraflari temizle
        $out = preg_replace('#<p>(\s|&nbsp;|<br>)*</p>#u', '', $out) ?? $out;
        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            // Once alt dugumleri temizle
            self::walk($child);

            if (!array_key_exists($tag, self::ALLOWED)) {
                // Etiketi kaldir, icindekileri yerinde birak (div, span, font vb.)
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, self::ALLOWED[$tag]);

            if ($tag === 'img' && !$child->hasAttribute('src')) {
                $node->removeChild($child);
            }
        }
    }

    /** @param string[] $allowed */
    private static function cleanAttributes(DOMElement $el, array $allowed): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            if (!in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        if ($el->hasAttribute('href')) {
            $href = self::safeUrl($el->getAttribute('href'), true);
            if ($href === null) {
                $el->removeAttribute('href');
            } else {
                $el->setAttribute('href', $href);
            }
        }
        if ($el->hasAttribute('src')) {
            $src = self::safeUrl($el->getAttribute('src'), false);
            if ($src === null) {
                $el->removeAttribute('src');
            } else {
                $el->setAttribute('src', $src);
            }
        }
        if ($el->hasAttribute('target')) {
            if ($el->getAttribute('target') === '_blank') {
                $el->setAttribute('rel', 'noopener noreferrer');
            } else {
                $el->removeAttribute('target');
            }
        }
        foreach (['width', 'height'] as $dim) {
            if ($el->hasAttribute($dim) && !ctype_digit($el->getAttribute($dim))) {
                $el->removeAttribute($dim);
            }
        }
    }

    /**
     * Izinli adresler: https://, http://, site ici yol (/...), sayfa ici capa (#...),
     * baglantilarda ayrica mailto: ve tel:. Diger her sey (javascript:, data: vb.) reddedilir.
     */
    private static function safeUrl(string $url, bool $isLink): ?string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        // Gorunmez karakterlerle gizlenmis "java\tscript:" gibi denemeler
        $compact = strtolower(preg_replace('/[\x00-\x20\x7f]+/', '', $url) ?? '');

        if ($compact === '') {
            return null;
        }
        if (preg_match('#^https?://#', $compact)) {
            return $url;
        }
        if (str_starts_with($compact, '//')) {
            return null;
        }
        if ($compact[0] === '/' || ($isLink && $compact[0] === '#')) {
            return $url;
        }
        if ($isLink && preg_match('#^(mailto|tel):#', $compact)) {
            return $url;
        }
        // Sema icermeyen goreli yol (uploads/... gibi)
        if (!preg_match('#^[a-z][a-z0-9+.\-]*:#', $compact)) {
            return $url;
        }
        return null;
    }
}
