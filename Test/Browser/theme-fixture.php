<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap.php';
$source = file_get_contents($argv[1]);
$scoped = (new \MageOS\Widgetkit\Model\PreviewCss())->transform($source, '[data-widgetkit-preview="theme-test"]');
$content = '<div class="p-4 text-lg">Text</div><button class="btn btn-primary">Button</button>'
    . '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"><div class="card">Card</div><div class="card">Card</div></div>'
    . '<div class="border rounded-lg p-2 mt-4">Border</div>';
$html = '<!doctype html><html><head><meta charset="utf-8"><title>Compiled theme preview parity</title>'
    . '<style>html{font-size:10px}body{font-family:serif;color:black}</style><style>' . $scoped . '</style></head><body>';
foreach (['desktop' => 1200, 'mobile' => 390] as $mode => $width) {
    $frame = '<!doctype html><html><head><style>' . $source . '</style></head><body>' . $content . '</body></html>';
    $html .= '<iframe id="reference-' . $mode . '" style="border:0;width:' . $width . 'px" srcdoc="' . htmlspecialchars($frame, ENT_QUOTES, 'UTF-8') . '"></iframe>';
    $html .= '<div id="preview-' . $mode . '" class="pagebuilder-stage-wrapper" style="width:1200px">'
        . '<div class="pagebuilder-canvas" style="width:' . $width . 'px"><div data-widgetkit-preview="theme-test">' . $content . '</div></div></div>';
}
$html .= '</body></html>';
file_put_contents($argv[2], $html);
