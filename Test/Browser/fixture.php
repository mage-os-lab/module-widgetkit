<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap.php';
$css = (new \MageOS\Widgetkit\Model\PreviewCss())->transform(
    file_get_contents(dirname(__DIR__) . '/Fixtures/preview.css'), '[data-widgetkit-preview="test"]');
$cards = '<div class="layered">Layer</div><div class="responsive">Responsive</div>'
    . '<div class="sized">Sized</div><div class="nested"><strong>Nested</strong></div>'
    . '<div class="device">Device</div><div class="nested-media">Nested media</div>'
    . '<div class="literal"></div>';
$html = '<!doctype html><html><head><meta charset="utf-8"><title>Widgetkit regression checks</title>'
    . '<style>html{font-size:10px}body{color:black;background:white}.pagebuilder-stage-wrapper{margin:10px}</style>'
    . '<style>' . $css . '</style></head><body><div id="outside" class="layered">Outside</div>';
foreach (['desktop' => 1200, 'mobile' => 390] as $viewport => $width) {
    $html .= '<div id="' . $viewport . '" class="pagebuilder-stage-wrapper" style="width:1200px">'
        . '<div class="pagebuilder-canvas" style="width:' . $width . 'px"><div data-widgetkit-preview="test">' . $cards . '</div></div></div>';
}
$html .= '</body></html>';
file_put_contents($argv[1], $html);
