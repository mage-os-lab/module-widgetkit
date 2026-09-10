<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use MageOS\Widgetkit\Model\RichTextDecoder;
use MageOS\Widgetkit\Model\WidgetOutput;
use MageOS\Widgetkit\Model\PreviewCss;

final class OutputTest extends TestCase
{
    public function testRichTextSanitizesAfterEveryEncodingLayer(): void
    {
        $payload = '<p onclick="alert(1)">Text <strong>bold</strong></p><svg onload="alert(1)"></svg>'
            . '<script>alert(1)</script><a href="java&#x09;script:alert(1)">Link</a>'
            . '<img src="x" onerror="alert(1)"><div style="background:url(javascript:alert(1))">CSS</div>';
        for ($i = 0; $i < 4; $i++) {
            $payload = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
            $safe = RichTextDecoder::html($payload);
            self::assertStringContainsString('<strong>bold</strong>', $safe);
            self::assertDoesNotMatchRegularExpression('/<script|<svg|onerror=|onclick=|onload=|javascript:/i', $safe);
        }
    }
    public function testLinkedCardContentCannotCreateNestedAnchors(): void
    {
        self::assertStringNotContainsString('<a ', RichTextDecoder::html('<p><a href="https://example.test">Link</a></p>', false));
        self::assertStringContainsString('<a ', RichTextDecoder::html('<p><a href="https://example.test">Link</a></p>'));
    }
    public function testIconRejectsActiveContentAndExternalReferences(): void
    {
        $fallback = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h1"/></svg>';
        foreach (['<svg onload="alert(1)"/>', '<svg><script>alert(1)</script></svg>',
            '<svg><use href="https://example.test/icon.svg"/></svg>', '<!DOCTYPE svg><svg/>',
            '<svg><path fill="url(https://example.test/paint)"/></svg>'] as $value) {
            self::assertSame($fallback, WidgetOutput::icon($value, $fallback));
        }
        self::assertStringContainsString('M0 0h1', WidgetOutput::icon($fallback, 'fallback'));
    }
    public function testHeadingAndGridPlacementAreValidated(): void
    {
        self::assertSame('h2', WidgetOutput::heading('h3 onclick="alert(1)"'));
        self::assertSame('h3', WidgetOutput::heading('h3'));
        self::assertSame('col-start-2 lg:col-end-7', WidgetOutput::gridClasses([
            'col_start_global' => 'col-start-2', 'col_start_tablet' => 'lg:col-start-1',
            'col_start_desktop' => 'fixed', 'col_end_desktop' => 'lg:col-end-7'
        ]));
    }
    public function testCssPreservesNestedSyntaxAndLiterals(): void
    {
        $css = (new PreviewCss())->transform(file_get_contents(__DIR__ . '/../Fixtures/preview.css'), '.preview');
        self::assertStringContainsString('@layer utilities', $css);
        self::assertStringContainsString('& strong', $css);
        self::assertStringContainsString('content: "@media (min-width: 1px) { :root }"', $css);
        self::assertStringContainsString('@supports (display: grid)', $css);
        self::assertStringContainsString('@container widgetkit-stage (width >= 768px)', $css);
        self::assertStringContainsString('prefers-reduced-motion: no-preference', $css);
        self::assertStringContainsString(':scope, :scope', $css);
        self::assertStringContainsString('font-size: 18px', $css);
    }
    public function testCssDoesNotRewriteSelectorsOrUrlLiteralsAsLengths(): void
    {
        $css = (new PreviewCss())->transform('.p-1rem {--var-1rem: 2rem; margin:-0.5rem; content:"3rem";background:url(/image-4rem.svg)}', '.preview');
        self::assertStringContainsString('.p-1rem', $css);
        self::assertStringContainsString('--var-1rem: 32px', $css);
        self::assertStringContainsString('margin:-8px', $css);
        self::assertStringContainsString('content:"3rem"', $css);
        self::assertStringContainsString('url(/image-4rem.svg)', $css);
    }
}
