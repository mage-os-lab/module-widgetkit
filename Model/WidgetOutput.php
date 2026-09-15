<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

/** Validation shared by storefront and PageBuilder templates. Escape attributes at their output sink. */
final class WidgetOutput
{
    public static function heading(?string $value, string $default = 'h2'): string
    {
        return in_array($value, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span'], true)
            ? $value : $default;
    }

    public static function duration(mixed $seconds, int $default = 3): int
    {
        if (!is_numeric($seconds) || !is_finite((float)$seconds) || (float)$seconds <= 0) {
            $seconds = $default;
        }
        return (int)(max(1, min(3600, (float)$seconds)) * 1000);
    }

    public static function gridClasses(array $row): string
    {
        $classes = [];
        foreach (['global' => '', 'tablet' => 'md:', 'desktop' => 'lg:'] as $viewport => $prefix) {
            foreach (['start', 'end'] as $edge) {
                $value = $row['col_' . $edge . '_' . $viewport] ?? '';
                if (is_string($value) && preg_match('/^' . $prefix . 'col-' . $edge . '-[1-7]$/D', $value)) {
                    $classes[] = $value;
                }
            }
        }
        return implode(' ', $classes);
    }

    /** Only inert, self-contained SVG shapes are accepted for a custom accordion icon. */
    public static function icon(?string $value, string $fallback): string
    {
        $value = RichTextDecoder::decode($value);
        if ($value === '' || preg_match('/<!|<\?/', $value)) {
            return $fallback;
        }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            if (!$document->loadXML($value, LIBXML_NONET) || $document->documentElement->tagName !== 'svg') {
                return $fallback;
            }
            $tags = ['svg', 'g', 'path', 'polyline', 'polygon', 'line', 'rect', 'circle', 'ellipse', 'title', 'desc'];
            $attributes = ['xmlns', 'viewBox', 'width', 'height', 'class', 'fill', 'stroke', 'stroke-width',
                'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray', 'stroke-dashoffset', 'fill-rule',
                'clip-rule', 'opacity', 'fill-opacity', 'stroke-opacity', 'd', 'points', 'x', 'y', 'x1',
                'x2', 'y1', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'transform', 'role', 'aria-hidden'];
            foreach ($document->getElementsByTagName('*') as $node) {
                if (!in_array($node->tagName, $tags, true)
                    || !in_array($node->namespaceURI, [null, '', 'http://www.w3.org/2000/svg'], true)) {
                    return $fallback;
                }
                foreach ($node->attributes as $attribute) {
                    if (!in_array($attribute->nodeName, $attributes, true)
                        || ($attribute->namespaceURI && $attribute->nodeName !== 'xmlns')
                        || preg_match('/url\s*\(|[\\\\<>]/i', $attribute->value)) {
                        return $fallback;
                    }
                }
            }
            return $document->saveXML($document->documentElement);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
