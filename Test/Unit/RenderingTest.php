<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class RenderingTest extends TestCase
{
    private function widget(string $name, ?callable $change = null): TemplateHarness
    {
        $params = TemplateHarness::fixture('mageos_' . $name);
        if ($change) { $change($params); }
        return new TemplateHarness(['params' => $params, 'items' => $params['repeatable_' . $name . '_items'] ?? []]);
    }
    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<!doctype html><html><body>' . $html . '</body></html>');
        return new DOMXPath($dom);
    }
    public function testEncodedPlainTitleCannotCreateAnElement(): void
    {
        foreach (['view/base/templates/widget/hyva/slider.phtml', 'view/adminhtml/templates/slider.phtml'] as $file) {
            $params = TemplateHarness::fixture('mageos_slider');
            $params['mageos_slider_title'] = '&amp;lt;script&amp;gt;window.__probe=1&amp;lt;/script&amp;gt;';
            $html = (new TemplateHarness($params))->render($file);
            self::assertSame(0, $this->xpath($html)->query('//script[contains(., "__probe")]')->length, $file);
        }
    }
    public function testRichTextKeepsFormattingAndRemovesExecutableContent(): void
    {
        foreach (['grid', 'accordion', 'tab', 'marquee', 'slider'] as $name) {
            $widget = $this->widget($name, function (&$params) use ($name) {
                $params['repeatable_' . $name . '_items'][0]['content'] =
                    '&lt;p onclick="window.__probe=1"&gt;Formatted &lt;strong&gt;content&lt;/strong&gt;&lt;/p&gt;'
                    . '&lt;script&gt;window.__probe=2&lt;/script&gt;'
                    . '&lt;a href="javascript:window.__probe=3"&gt;link&lt;/a&gt;';
            });
            $xp = $this->xpath($widget->render("view/base/templates/widget/hyva/$name/template.phtml"));
            self::assertGreaterThan(0, $xp->query('//p/strong')->length, $name);
            self::assertSame(0, $xp->query('//*[@onclick] | //script[contains(., "__probe")] | //a[starts-with(@href, "javascript:")]')->length, $name);
        }
    }
    public function testSlideshowAttributesAndTagsAreSafe(): void
    {
        $widget = $this->widget('slideshow', function (&$p) {
            $p['repeatable_slideshow_items'][0] = array_replace($p['repeatable_slideshow_items'][0], [
                'image' => 'missing" onerror="window.__probe=1',
                'title_tag' => 'h3 onclick="window.__probe=1"', 'button_link' => 'javascript:window.__probe=1']);
            $p['mageos_slideshow_overlay_title_classes'] = 'x" onclick="window.__probe=1';
            $p['mageos_slideshow_transition'] = "';window.__probe=1;//";
        });
        $html = $widget->render('view/base/templates/widget/hyva/slideshow/template.phtml');
        self::assertSame(0, $this->xpath($html)->query('//*[@onclick or @onerror] | //a[starts-with(@href, "javascript:")]')->length);
        self::assertStringNotContainsString("transition: '';window.__probe", $html);
    }
    public function testAccordionCustomSvgCannotExecuteCode(): void
    {
        $widget = $this->widget('accordion', function (&$params) {
            $params['mageos_accordion_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" onload="window.__probe=1"><script>window.__probe=1</script></svg>';
        });
        $xp = $this->xpath($widget->render('view/base/templates/widget/hyva/accordion/template.phtml'));
        self::assertSame(0, $xp->query('//*[@onload] | //script[contains(., "__probe")]')->length);
        self::assertSame(1, $xp->query('//svg')->length);
    }
    public function testAllPlainWidgetHeadingsEscapeClassesAndValidateTags(): void
    {
        foreach (['slider', 'slideshow', 'grid', 'information_grid', 'product_grid', 'product_slider', 'accordion', 'tab', 'marquee'] as $name) {
            $params = TemplateHarness::fixture('mageos_' . $name);
            $params['mageos_' . $name . '_title'] = '&lt;script&gt;window.__probe=1&lt;/script&gt;';
            $params['mageos_' . $name . '_title_type'] = 'h2 onclick="window.__probe=1"';
            $params['mageos_' . $name . '_title_classes'] = 'x" onmouseover="window.__probe=1';
            foreach (['view/base/templates/widget/hyva/', 'view/adminhtml/templates/'] as $directory) {
                $html = (new TemplateHarness($params))->render($directory . str_replace('_', '-', $name) . '.phtml');
                self::assertSame(0, $this->xpath($html)->query('//*[@onclick or @onmouseover] | //script[contains(., "__probe")]')->length, $name);
            }
        }
    }
    public function testSlideshowRejectsInvalidSpeed(): void
    {
        $widget = $this->widget('slideshow', function (&$p) { $p['mageos_slideshow_speed'] = 'not-a-number'; });
        self::assertStringContainsString('autoplayDuration: 3000', $widget->render('view/base/templates/widget/hyva/slideshow/template.phtml'));
    }
    public function testOptionalImageHasNoEmptyPicture(): void
    {
        self::assertSame(0, $this->xpath($this->widget('slider')->render('view/base/templates/widget/hyva/slider/template.phtml'))->query('//picture')->length);
    }
    public function testGridWithButtonDoesNotNestLinks(): void
    {
        $html = $this->widget('grid')->render('view/base/templates/widget/hyva/grid/template.phtml');
        // Count the source, because an HTML parser repairs nested anchors automatically.
        self::assertSame(1, preg_match_all('/<a\s/', $html));
    }
    public function testMarqueeOriginalRemainsAccessible(): void
    {
        $xp = $this->xpath($this->widget('marquee')->render('view/base/templates/widget/hyva/marquee/template.phtml'));
        self::assertSame(1, $xp->query('//div[contains(@class,"marquee-item ") and not(@aria-hidden="true")]')->length);
        self::assertSame(1, $xp->query('//div[contains(@class,"marquee-item ") and @aria-hidden="true" and @inert]')->length);
    }
}
