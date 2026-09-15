<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model\Css;

/**
 * Rewrites CSS Color 4 syntax (oklch()/oklab(), and any color-mix()) into legacy rgb()/rgba()
 * so the stylesheet stays readable by consumers that only understand pre-2023 CSS colors -
 * namely the html2canvas build bundled with Magento_PageBuilder, which throws
 * `Attempting to parse an unsupported color function "oklch"` when it walks an element styled
 * with either syntax while generating the "Save as Template" thumbnail.
 *
 * This is applied only to the cached admin/preview copy of the theme's CSS
 * (see PreviewStylesheet) - the real, compiled theme stylesheet served to storefront visitors
 * is never touched, so wide-gamut oklch colors are preserved there.
 *
 * Deliberately narrow in scope: it only resolves what Tailwind v4's default build actually
 * emits (oklch() color values, and color-mix() used for opacity modifiers). A color-mix() term
 * it cannot resolve (currentColor, an unknown custom property, ...) is left untouched rather
 * than guessed at - it may still trip html2canvas, but that's the same outcome as before this
 * class existed, not a regression.
 */
class ModernColorDowngrader
{
    private const NAMED_COLORS = [
        'transparent' => [0, 0, 0, 0.0],
        'black' => [0, 0, 0, 1.0],
        'white' => [255, 255, 255, 1.0],
        'red' => [255, 0, 0, 1.0],
        'green' => [0, 128, 0, 1.0],
        'blue' => [0, 0, 255, 1.0],
    ];

    /**
     * @param string $css
     * @return string
     */
    public function downgrade(string $css): string
    {
        $css = $this->convertOklchFunctions($css);

        // color-mix() arguments can themselves reference custom properties that were only
        // just downgraded above, and (rarely) a color-mix() could be nested in another one -
        // re-resolve until nothing changes rather than assume one pass is enough.
        for ($i = 0; $i < 5; $i++) {
            $next = $this->convertColorMixFunctions($css);
            if ($next === $css) {
                break;
            }
            $css = $next;
        }

        return $css;
    }

    /**
     * @param string $css
     * @return string
     */
    private function convertOklchFunctions(string $css): string
    {
        return (string) preg_replace_callback(
            '/\boklch\(\s*([^()]+?)\s*\)/i',
            fn (array $m) => $this->renderRgb($this->parseOklch($m[1])),
            $css
        );
    }

    /**
     * @param string $args
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function parseOklch(string $args): array
    {
        [$main, $alphaToken] = array_pad(explode('/', $args, 2), 2, null);
        $parts = preg_split('/[\s,]+/', trim((string) $main)) ?: [];
        $l = $this->parseComponent($parts[0] ?? '0', 1.0);
        $c = $this->parseComponent($parts[1] ?? '0', 0.4);
        $h = $this->parseAngle($parts[2] ?? '0');
        $alpha = $alphaToken !== null ? $this->parseComponent(trim($alphaToken), 1.0) : 1.0;

        $hRad = deg2rad($h);
        $a = $c * cos($hRad);
        $b = $c * sin($hRad);

        return $this->oklabToRgb($l, $a, $b, $alpha);
    }

    /**
     * @param float $l
     * @param float $a
     * @param float $b
     * @param float $alpha
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function oklabToRgb(float $l, float $a, float $b, float $alpha): array
    {
        $l_ = $l + 0.3963377774 * $a + 0.2158037573 * $b;
        $m_ = $l - 0.1055613458 * $a - 0.0638541728 * $b;
        $s_ = $l - 0.0894841775 * $a - 1.2914855480 * $b;

        $l3 = $l_ ** 3;
        $m3 = $m_ ** 3;
        $s3 = $s_ ** 3;

        $rLin = 4.0767416621 * $l3 - 3.3077115913 * $m3 + 0.2309699292 * $s3;
        $gLin = -1.2684380046 * $l3 + 2.6097574011 * $m3 - 0.3413193965 * $s3;
        $bLin = -0.0041960863 * $l3 - 0.7034186147 * $m3 + 1.7076147010 * $s3;

        return [
            (int) round($this->gammaEncode($rLin) * 255),
            (int) round($this->gammaEncode($gLin) * 255),
            (int) round($this->gammaEncode($bLin) * 255),
            $alpha,
        ];
    }

    /**
     * @param float $linear
     * @return float
     */
    private function gammaEncode(float $linear): float
    {
        $linear = max(0.0, min(1.0, $linear));

        return $linear <= 0.0031308
            ? $linear * 12.92
            : 1.055 * ($linear ** (1 / 2.4)) - 0.055;
    }

    /**
     * @param float $r 0-1
     * @param float $g 0-1
     * @param float $b 0-1
     * @return array{0: float, 1: float, 2: float} OKLab [L, a, b]
     */
    private function srgbToOklab(float $r, float $g, float $b): array
    {
        $rLin = $this->gammaDecode($r);
        $gLin = $this->gammaDecode($g);
        $bLin = $this->gammaDecode($b);

        $l = 0.4122214708 * $rLin + 0.5363325363 * $gLin + 0.0514459929 * $bLin;
        $m = 0.2119034982 * $rLin + 0.6806995451 * $gLin + 0.1073969566 * $bLin;
        $s = 0.0883024619 * $rLin + 0.2817188376 * $gLin + 0.6299787005 * $bLin;

        $l_ = $this->signedCbrt($l);
        $m_ = $this->signedCbrt($m);
        $s_ = $this->signedCbrt($s);

        return [
            0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_,
            1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_,
            0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_,
        ];
    }

    /**
     * @param float $encoded 0-1
     * @return float
     */
    private function gammaDecode(float $encoded): float
    {
        return $encoded <= 0.04045
            ? $encoded / 12.92
            : (($encoded + 0.055) / 1.055) ** 2.4;
    }

    /**
     * @param float $x
     * @return float
     */
    private function signedCbrt(float $x): float
    {
        return $x < 0 ? -((-$x) ** (1 / 3)) : $x ** (1 / 3);
    }

    /**
     * @param string $token
     * @param float $hundredPercentValue value that a literal "100%" represents for this component
     * @return float
     */
    private function parseComponent(string $token, float $hundredPercentValue): float
    {
        $token = trim($token);
        if ($token === '' || strcasecmp($token, 'none') === 0) {
            return 0.0;
        }
        if (str_ends_with($token, '%')) {
            return ((float) rtrim($token, '%')) / 100 * $hundredPercentValue;
        }

        return (float) $token;
    }

    /**
     * @param string $token
     * @return float degrees
     */
    private function parseAngle(string $token): float
    {
        $token = trim($token);
        if ($token === '' || strcasecmp($token, 'none') === 0) {
            return 0.0;
        }

        return (float) preg_replace('/deg$/i', '', $token);
    }

    /**
     * @param array{0: int, 1: int, 2: int, 3: float} $rgba
     * @return string
     */
    private function renderRgb(array $rgba): string
    {
        [$r, $g, $b, $a] = $rgba;
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));
        $a = max(0.0, min(1.0, $a));

        if ($a >= 1.0) {
            return "rgb({$r}, {$g}, {$b})";
        }

        $alpha = rtrim(rtrim(number_format($a, 3), '0'), '.');

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    /**
     * @param string $css
     * @return string
     */
    private function convertColorMixFunctions(string $css): string
    {
        return (string) preg_replace_callback(
            '/\bcolor-mix\(\s*in\s+([a-z0-9-]+)(?:\s+(?:shorter|longer|increasing|decreasing)\s+hue)?\s*,\s*'
            . '((?:[^()]|\([^()]*\))*)\)/i',
            function (array $m) use ($css) {
                $resolved = $this->resolveColorMix($m[1], $m[2], $css);

                return $resolved !== null ? $this->renderRgb($resolved) : $m[0];
            },
            $css
        );
    }

    /**
     * @param string $space
     * @param string $argsList the two "<color> [<percentage>]?" terms, comma-separated
     * @param string $css full stylesheet text, used to resolve var(--custom-property) terms
     * @return array{0: int, 1: int, 2: int, 3: float}|null null when a term can't be resolved
     */
    private function resolveColorMix(string $space, string $argsList, string $css): ?array
    {
        $terms = $this->splitTopLevel($argsList);
        if (count($terms) !== 2) {
            return null;
        }

        [$term1, $pct1] = $this->splitTermAndPercentage($terms[0]);
        [$term2, $pct2] = $this->splitTermAndPercentage($terms[1]);

        $color1 = $this->resolveColorTerm($term1, $css);
        $color2 = $this->resolveColorTerm($term2, $css);
        if ($color1 === null || $color2 === null) {
            return null;
        }

        if ($pct1 === null && $pct2 === null) {
            $pct1 = $pct2 = 50.0;
        } elseif ($pct1 === null) {
            $pct1 = 100.0 - $pct2;
        } elseif ($pct2 === null) {
            $pct2 = 100.0 - $pct1;
        }

        $alphaMultiplier = 1.0;
        $sum = $pct1 + $pct2;
        if (abs($sum - 100.0) > 0.001) {
            if ($sum <= 0.0) {
                $pct1 = $pct2 = 50.0;
            } else {
                $alphaMultiplier = min(1.0, $sum / 100);
                $pct1 = $pct1 / $sum * 100;
                $pct2 = $pct2 / $sum * 100;
            }
        }

        $useOklab = in_array(strtolower($space), ['oklab', 'oklch'], true);
        $mixed = $useOklab
            ? $this->mixInOklab($color1, $color2, $pct1, $pct2)
            : $this->mixInSrgb($color1, $color2, $pct1, $pct2);

        $mixed[3] = max(0.0, min(1.0, $mixed[3] * $alphaMultiplier));

        return $mixed;
    }

    /**
     * Mix two colors "in oklab" per the CSS Color 4 premultiplied-alpha interpolation algorithm.
     *
     * @param array{0: int, 1: int, 2: int, 3: float} $c1
     * @param array{0: int, 1: int, 2: int, 3: float} $c2
     * @param float $pct1
     * @param float $pct2
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function mixInOklab(array $c1, array $c2, float $pct1, float $pct2): array
    {
        $lab1 = $this->srgbToOklab($c1[0] / 255, $c1[1] / 255, $c1[2] / 255);
        $lab2 = $this->srgbToOklab($c2[0] / 255, $c2[1] / 255, $c2[2] / 255);

        [$l, $a, $b, $alpha] = $this->premultipliedMix($lab1, $c1[3], $lab2, $c2[3], $pct1, $pct2);

        return $this->oklabToRgb($l, $a, $b, $alpha);
    }

    /**
     * Mix two colors directly in sRGB space. Used for "in srgb" (the exact colorspace already),
     * and as a pragmatic fallback for any mixing space this class doesn't model explicitly
     * (lab, lch, hsl, hwb, xyz, ...) - close enough for an admin-only thumbnail, and always
     * better than leaving an unparseable color-mix() behind for html2canvas to choke on.
     *
     * @param array{0: int, 1: int, 2: int, 3: float} $c1
     * @param array{0: int, 1: int, 2: int, 3: float} $c2
     * @param float $pct1
     * @param float $pct2
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function mixInSrgb(array $c1, array $c2, float $pct1, float $pct2): array
    {
        [$r, $g, $b, $alpha] = $this->premultipliedMix(
            [$c1[0] / 255, $c1[1] / 255, $c1[2] / 255],
            $c1[3],
            [$c2[0] / 255, $c2[1] / 255, $c2[2] / 255],
            $c2[3],
            $pct1,
            $pct2
        );

        return [(int) round($r * 255), (int) round($g * 255), (int) round($b * 255), $alpha];
    }

    /**
     * @param array{0: float, 1: float, 2: float} $comp1
     * @param float $alpha1
     * @param array{0: float, 1: float, 2: float} $comp2
     * @param float $alpha2
     * @param float $pct1
     * @param float $pct2
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function premultipliedMix(
        array $comp1,
        float $alpha1,
        array $comp2,
        float $alpha2,
        float $pct1,
        float $pct2
    ): array {
        $w1 = $pct1 / 100;
        $w2 = $pct2 / 100;
        $resultAlpha = $alpha1 * $w1 + $alpha2 * $w2;

        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $premixed = $comp1[$i] * $alpha1 * $w1 + $comp2[$i] * $alpha2 * $w2;
            $result[] = $resultAlpha > 0 ? $premixed / $resultAlpha : 0.0;
        }

        $result[] = $resultAlpha;

        return $result;
    }

    /**
     * Split a color-mix() argument list on its top-level commas only (a "," inside a
     * var(--x, fallback) must stay with that term).
     *
     * @param string $argsList
     * @return string[]
     */
    private function splitTopLevel(string $argsList): array
    {
        $parts = [];
        $depth = 0;
        $current = '';
        foreach (str_split($argsList) as $char) {
            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }
            if ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }
            $current .= $char;
        }
        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    /**
     * @param string $term e.g. "var(--color-red-500) 50%" or "transparent"
     * @return array{0: string, 1: float|null} [color term, percentage or null]
     */
    private function splitTermAndPercentage(string $term): array
    {
        $term = trim($term);
        if (preg_match('/^(.*\S)\s+(\d+(?:\.\d+)?)%$/', $term, $m)) {
            return [$m[1], (float) $m[2]];
        }

        return [$term, null];
    }

    /**
     * @param string $term
     * @param string $css full stylesheet text, to look up custom property declarations in
     * @return array{0: int, 1: int, 2: int, 3: float}|null
     */
    private function resolveColorTerm(string $term, string $css): ?array
    {
        $term = trim($term);

        if (preg_match('/^var\(\s*(--[\w-]+)\s*\)$/i', $term, $m)) {
            return $this->resolveCustomProperty($m[1], $css);
        }

        $lower = strtolower($term);
        if (isset(self::NAMED_COLORS[$lower])) {
            return self::NAMED_COLORS[$lower];
        }

        if (preg_match('/^#([0-9a-f]{3,8})$/i', $term, $m)) {
            return $this->parseHexColor($m[1]);
        }

        if (preg_match('/^rgba?\(\s*([^()]+)\)$/i', $term, $m)) {
            return $this->parseRgbFunction($m[1]);
        }

        // currentcolor and anything else (lab()/lch()/hsl() literals, unknown keywords, ...)
        // can't be resolved statically here - bail out and leave the color-mix() untouched.
        return null;
    }

    /**
     * @param string $name e.g. "--color-red-500"
     * @param string $css
     * @return array{0: int, 1: int, 2: int, 3: float}|null
     */
    private function resolveCustomProperty(string $name, string $css): ?array
    {
        $pattern = '/' . preg_quote($name, '/') . '\s*:\s*([^;}!]+)/i';
        if (!preg_match($pattern, $css, $m)) {
            return null;
        }

        return $this->resolveColorTerm(trim($m[1]), $css);
    }

    /**
     * @param string $hex 3, 4, 6 or 8 hex digits (without '#')
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function parseHexColor(string $hex): array
    {
        if (strlen($hex) <= 4) {
            $hex = implode('', array_map(fn ($c) => $c . $c, str_split($hex)));
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $a = strlen($hex) >= 8 ? hexdec(substr($hex, 6, 2)) / 255 : 1.0;

        return [$r, $g, $b, $a];
    }

    /**
     * @param string $args e.g. "255, 0, 0" or "255 0 0 / 50%"
     * @return array{0: int, 1: int, 2: int, 3: float}
     */
    private function parseRgbFunction(string $args): array
    {
        [$main, $alphaToken] = array_pad(explode('/', $args, 2), 2, null);
        $parts = preg_split('/[\s,]+/', trim((string) $main)) ?: [];

        $channel = fn (string $t): int => (int) round(
            str_ends_with($t, '%') ? ((float) rtrim($t, '%')) / 100 * 255 : (float) $t
        );

        return [
            $channel($parts[0] ?? '0'),
            $channel($parts[1] ?? '0'),
            $channel($parts[2] ?? '0'),
            $alphaToken !== null ? $this->parseComponent(trim($alphaToken), 1.0) : 1.0,
        ];
    }
}
