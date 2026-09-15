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
    private static ?\HTMLPurifier $purifier = null;

    /** Decode every serialization layer before sanitizing intended WYSIWYG HTML. */
    public static function html(?string $value, bool $allowLinks = true): string
    {
        if (self::$purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);
            $config->set('HTML.Allowed', 'p,br,div,span,strong,b,em,i,u,s,sub,sup,blockquote,pre,code,'
                . 'h1,h2,h3,h4,h5,h6,ul,ol,li,hr,table,thead,tbody,tfoot,tr,th,td,'
                . 'a[href|title],img[src|alt|width|height],*[class|style]');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
            self::$purifier = new \HTMLPurifier($config);
        }
        $html = self::$purifier->purify(self::decode($value));
        // A linked card must not contain another anchor from its WYSIWYG content.
        return $allowLinks ? $html : preg_replace('~</?a\b[^>]*>~i', '', $html);
    }

    /**
     * Decode only; callers must escape for their output context or use html().
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
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $changed = $decoded !== $value;
            $value = $decoded;
        } while ($changed);

        return $value;
    }
}
