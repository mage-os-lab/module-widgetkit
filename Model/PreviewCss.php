<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

/**
 * Keep compiled CSS intact, including layers, nesting and custom properties. The browser
 * scopes selectors; only viewport-width conditions and document anchors need translation.
 */
final class PreviewCss
{
    public const CONTAINER = 'widgetkit-stage';

    public function __construct(private float $rootFontSize = 16.0)
    {
    }

    public function getCacheKey(): string
    {
        return hash('sha256', (string)$this->rootFontSize . hash_file('sha256', __FILE__));
    }

    public function transform(string $css, string $scope): string
    {
        // PageBuilder resizes the canvas in mobile mode; the outer wrapper stays full width.
        return '.pagebuilder-stage-wrapper .pagebuilder-canvas{container: ' . self::CONTAINER . ' / inline-size;}'
            . "\n@scope (" . $scope . ') {:scope{font-size:' . $this->rootFontSize . 'px;}' . $this->rewrite($css) . '}';
    }

    private function rewrite(string $css): string
    {
        $output = '';
        $start = 0;
        $length = strlen($css);
        for ($i = 0; $i < $length; $i++) {
            if ($this->skipLiteral($css, $i)) {
                continue;
            }
            if ($css[$i] === '(' || $css[$i] === '[') {
                $i = $this->closing($css, $i, $css[$i] === '(' ? ')' : ']');
            } elseif ($css[$i] === ';') {
                $statement = substr($css, $start, $i - $start + 1);
                if (preg_match('/^\s*@import\b/i', $statement)) {
                    throw new \InvalidArgumentException('Compile CSS imports before generating a widget preview.');
                }
                $output .= $this->remUnits($statement);
                $start = $i + 1;
            } elseif ($css[$i] === '{') {
                $end = $this->closing($css, $i, '}');
                $header = substr($css, $start, $i - $start);
                $body = $this->rewrite(substr($css, $i + 1, $end - $i - 1));
                // Comments are kept but must not obscure the following at-rule or selector.
                $plainHeader = preg_replace('~/\*.*?\*/~s', '', $header);
                if (preg_match('/^\s*@media\s*(.+)$/s', $plainHeader, $match)) {
                    $output .= $this->media($match[1], $body);
                } elseif (preg_match('/^\s*@(charset|import)\b/i', $plainHeader)) {
                    throw new \InvalidArgumentException('Unexpected uncompiled CSS directive.');
                } else {
                    if (!str_starts_with(ltrim($plainHeader), '@')) {
                        $header = $this->anchors($header);
                    }
                    $output .= $header . '{' . $body . '}';
                }
                $i = $end;
                $start = $end + 1;
            }
        }
        return $output . $this->remUnits(substr($css, $start));
    }

    /** Admin rem units use a different root size. Preserve the theme's configured baseline. */
    private function remUnits(string $value): string
    {
        $output = '';
        for ($i = 0; $i < strlen($value); $i++) {
            $start = $i;
            if ($this->skipLiteral($value, $i)) {
                $output .= substr($value, $start, $i - $start + 1);
            } elseif (strtolower(substr($value, $i, 4)) === 'url(') {
                $i = $this->closing($value, $i + 3, ')');
                $output .= substr($value, $start, $i - $start + 1);
            } elseif (($i === 0 || !preg_match('/[\\w.-]/', $value[$i - 1]))
                && preg_match('/\\G(-?\\d*\\.?(?:\\d+))rem(?![\\w-])/i', $value, $match, 0, $i)) {
                $output .= ((float)$match[1] * $this->rootFontSize) . 'px';
                $i += strlen($match[0]) - 1;
            } else { $output .= $value[$i]; }
        }
        return $output;
    }

    /** Skip strings, escapes and comments so their braces/keywords never become CSS syntax. */
    private function skipLiteral(string $css, int &$i): bool
    {
        if ($css[$i] === '\\') {
            $i++;
            return true;
        }
        if (substr($css, $i, 2) === '/*') {
            $end = strpos($css, '*/', $i + 2);
            if ($end === false) { throw new \InvalidArgumentException('Unclosed CSS comment.'); }
            $i = $end + 1;
            return true;
        }
        if ($css[$i] === '"' || $css[$i] === "'") {
            $quote = $css[$i];
            while (++$i < strlen($css)) {
                if ($css[$i] === '\\') { $i++; }
                elseif ($css[$i] === $quote) { return true; }
            }
            throw new \InvalidArgumentException('Unclosed CSS string.');
        }
        return false;
    }

    private function closing(string $css, int $start, string $closing): int
    {
        $opening = $css[$start];
        $depth = 1;
        for ($i = $start + 1; $i < strlen($css); $i++) {
            if ($this->skipLiteral($css, $i)) { continue; }
            if ($opening === '{' && ($css[$i] === '(' || $css[$i] === '[')) {
                $i = $this->closing($css, $i, $css[$i] === '(' ? ')' : ']');
                continue;
            }
            if ($css[$i] === $opening) { $depth++; }
            elseif ($css[$i] === $closing && --$depth === 0) { return $i; }
        }
        throw new \InvalidArgumentException('Unbalanced CSS block.');
    }

    private function anchors(string $selector): string
    {
        // Alter selector tokens only, never strings or attribute selector contents.
        $result = '';
        for ($i = 0; $i < strlen($selector); $i++) {
            $start = $i;
            if ($this->skipLiteral($selector, $i)) {
                $result .= substr($selector, $start, $i - $start + 1);
            } elseif ($selector[$i] === '[') {
                $i = $this->closing($selector, $i, ']');
                $result .= substr($selector, $start, $i - $start + 1);
            } elseif (($i === 0 || str_contains(" ,(>+~\n\t", $selector[$i - 1]))
                && preg_match('/\G(?::root|:host(?!\()|html|body)(?![\w-])/A', $selector, $m, 0, $i)) {
                $result .= ':scope';
                $i += strlen($m[0]) - 1;
            } else {
                $result .= $selector[$i];
            }
        }
        return $result;
    }

    /** Split only at top-level commas/AND, keeping nested media features intact. */
    private function split(string $condition, string $separator): array
    {
        $parts = [];
        $start = 0;
        for ($i = 0; $i < strlen($condition); $i++) {
            if ($this->skipLiteral($condition, $i)) { continue; }
            if ($condition[$i] === '(') { $i = $this->closing($condition, $i, ')'); }
            elseif (strtolower(substr($condition, $i, strlen($separator))) === $separator) {
                $parts[] = trim(substr($condition, $start, $i - $start));
                $i += strlen($separator) - 1;
                $start = $i + 1;
            }
        }
        $parts[] = trim(substr($condition, $start));
        return $parts;
    }

    private function media(string $condition, string $body): string
    {
        $output = '';
        foreach ($this->split($condition, ',') as $query) {
            $width = [];
            $device = [];
            foreach ($this->split($query, ' and ') as $part) {
                if (preg_match('/\b(?:min-|max-)?width\b/i', $part)) {
                    // Width queries can use range syntax, OR, and NOT. Other device features
                    // in the same parenthesized term cannot be evaluated by a size container.
                    $features = preg_replace('/(?:min-|max-)?width|\d*\.?\d+(?:px|rem|em)|\band\b|\bor\b|\bnot\b|[\s():<>=.\d-]/i', '', $part);
                    if ($features !== '') {
                        throw new \InvalidArgumentException('Unsupported mixed width/device media condition: ' . $part);
                    }
                    // Media em/rem units use the initial font size, not the admin's 10px rem.
                    $width[] = preg_replace_callback('/(\d*\.?\d+)(?:rem|em)\b/i',
                        static fn(array $m): string => ((float)$m[1] * 16) . 'px', $part);
                } elseif (!in_array(strtolower($part), ['all', 'only all'], true)) {
                    $device[] = $part;
                }
            }
            // Negating a media type negates the entire query. Do not silently change its logic.
            if ($width && $device && preg_match('/^not\s/i', $query)) {
                throw new \InvalidArgumentException('Compile negated mixed media queries before previewing.');
            }
            $rule = $body;
            if ($width) { $rule = '@container ' . self::CONTAINER . ' ' . implode(' and ', $width) . '{' . $rule . '}'; }
            if ($device) { $rule = '@media ' . implode(' and ', $device) . '{' . $rule . '}'; }
            $output .= $rule;
        }
        return $output;
    }
}
