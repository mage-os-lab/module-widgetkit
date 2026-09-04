<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

/**
 * PageBuilder round-trips widget rich-text parameters (description, finished_content, ...)
 * through its own JSON serialization and then Magento's widget-directive attribute embedding
 * before they ever reach a block's getData(): each layer HTML-encodes the value again, so a
 * plain "<p>foo</p>" a merchant types in the WYSIWYG editor ends up stored as
 * "&amp;lt;p&amp;gt;foo&amp;lt;/p&amp;gt;" - two layers deep, confirmed against a real saved
 * widget instance. A single html_entity_decode() only undoes one layer, leaving visible
 * "&lt;p&gt;" text in the rendered page. Decoding in a loop until the string stops changing
 * handles however many layers actually happened, instead of assuming a fixed count.
 */
class RichTextDecoder
{
    /**
     * @param string|null $value
     * @return string
     */
    public static function decode(?string $value): string
    {
        $value = (string)$value;
        if ($value === '') {
            return '';
        }

        do {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);
            $changed = $decoded !== $value;
            $value = $decoded;
        } while ($changed);

        return $value;
    }
}
