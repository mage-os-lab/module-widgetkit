<?php
declare(strict_types=1);
chdir(getenv('MAGENTO_ROOT') ?: throw new RuntimeException('Set MAGENTO_ROOT.'));
$output = $argv[1] ?? sys_get_temp_dir() . '/widgetkit-security-' . uniqid();
if (!is_dir($output)) { mkdir($output, 0700, true); }
require 'app/bootstrap.php';
require dirname(__DIR__) . '/Support/TemplateHarness.php';
$om=\Magento\Framework\App\Bootstrap::create(BP,$_SERVER)->getObjectManager();
$om->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
$om->configure($om->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)->load('frontend'));
$storeId = (int)($argv[2] ?? 1);
$om->get(\Magento\Store\Model\StoreManagerInterface::class)->setCurrentStore($storeId);
$design = $om->get(\Magento\Framework\View\DesignInterface::class);
$design->setArea('frontend')->setDesignTheme($design->getConfigurationDesignTheme('frontend', ['store' => $storeId]), 'frontend');
$widget=$om->get(\Magento\Widget\Model\Widget::class);
$filter=$om->get(\Magento\Widget\Model\Template\Filter::class);
$builder=(new ReflectionClass(\MageOS\PageBuilderWidget\Controller\Adminhtml\ContentType\Widget\Build::class))->newInstanceWithoutConstructor();
foreach(['escaper'=>$om->get(\Magento\Framework\Escaper::class),'objectManager'=>$om] as $key=>$value) (new ReflectionProperty($builder,$key))->setValue($builder,$value);
$fixtures=[];
foreach (simplexml_load_file(dirname(__DIR__, 2) . '/etc/widget.xml')->widget as $widgetConfig) {
    $fixtures[(string)$widgetConfig['id']] = ['class' => (string)$widgetConfig['class'], 'params' => TemplateHarness::fixture((string)$widgetConfig['id'])];
}
$tests=[
    'encoded-title'=>['widget'=>'mageos_slider','field'=>'mageos_slider_title','payload'=>'&lt;script&gt;window.__widgetkit_title=1&lt;/script&gt;'],
    'rich-content'=>['widget'=>'mageos_banner','field'=>'mageos_banner_content','payload'=>'&lt;script&gt;window.__widgetkit_content=1&lt;/script&gt;'],
    'link-protocol'=>['widget'=>'mageos_slideshow','row'=>'repeatable_slideshow_items','field'=>'button_link','payload'=>'javascript:window.__widgetkit_link=1'],
    'image-attribute'=>['widget'=>'mageos_slideshow','row'=>'repeatable_slideshow_items','field'=>'image','payload'=>'missing-review-image" onerror="window.__widgetkit_image=1'],
    'heading-attribute'=>['widget'=>'mageos_accordion','row'=>'repeatable_accordion_items','field'=>'title_tag','payload'=>'h3 onclick="window.__widgetkit_heading=1"'],
];
$out=[];$html='<!doctype html><meta charset="utf-8"><title>Widgetkit inert security reproduction</title>';
foreach($tests as $name=>$test){
    $fixture=$fixtures[$test['widget']];$params=$fixture['params'];
    if(isset($test['row'])) $params[$test['row']][0][$test['field']]=$test['payload']; else $params[$test['field']]=$test['payload'];
    $params['template']='MageOS_Widgetkit::'.$params['template'];
    $sanitized=$builder->sanitizeWidgetParams($params,$widget->getConfigAsObject($fixture['class']));
    $declArgs=$om->get(\MageOS\AdvancedWidget\Plugin\SaveRepeatableItems::class)->beforeGetWidgetDeclaration($widget,$fixture['class'],$sanitized,true);
    $directive=$widget->getWidgetDeclaration(...$declArgs);
    try{
        $rendered=$filter->filter($directive);
        $out[$name]=['sanitized_value'=>isset($test['row'])?$sanitized[$test['row']][0][$test['field']]:$sanitized[$test['field']], 'unsafe_nodes'=>unsafeNodes($rendered),'bytes'=>strlen($rendered)];
        $html.='<section id="'.$name.'"><h1>'.$name.'</h1>'.$rendered.'</section>';
    }catch(Throwable $e){$out[$name]=['error'=>get_class($e).': '.$e->getMessage()];}
}
file_put_contents($output . '/security-reproduction.html',$html);
file_put_contents($output . '/security-results.json',json_encode($out,JSON_PRETTY_PRINT));
echo json_encode($out,JSON_PRETTY_PRINT)."\n";

function unsafeNodes(string $html): int {
    $dom = new DOMDocument();
    @$dom->loadHTML('<!doctype html><html><body>' . $html . '</body></html>');
    $unsafe = (new DOMXPath($dom))->query('//*[@onerror or @onclick or @onload] | //a[starts-with(@href, "javascript:")]')->length;
    foreach ($dom->getElementsByTagName('script') as $script) {
        // A safely escaped URL can contain the marker as inert text in a legitimate script.
        if (preg_match('/window\\.__widgetkit_\\w+\\s*=/', $script->textContent)) { $unsafe++; }
    }
    return $unsafe;
}
exit(count(array_filter($out, fn($result) => isset($result['error']) || $result['unsafe_nodes'] !== 0)) ? 1 : 0);
