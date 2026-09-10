<?php
declare(strict_types=1);

/** Execute the real templates with real escaping/expression helpers, without a database. */
class TemplateHarness extends \MageOS\Widgetkit\Block\Widgets\HyvaWidget
{
    public function __construct(array $data = [])
    {
        $this->setData($data);
        $this->_escaper = new \Magento\Framework\Escaper();
        (new ReflectionProperty($this->_escaper, 'escaper'))->setValue($this->_escaper, new \Magento\Framework\ZendEscaper());
        $this->filterManager = new class($this->_escaper) {
            public function __construct(private $escaper) {}
            public function stripTags($value, $params) {
                return (new \Magento\Framework\Filter\StripTags($this->escaper, $params['allowableTags'], $params['escape']))->filter($value);
            }
        };
        (new ReflectionProperty($this->_escaper, 'translateInline'))->setValue($this->_escaper,
            new class { public function isAllowed(): bool { return false; } public function processResponseBody(&$body): void {} });
    }
    public function getChildHtml($alias = '', $useCache = true) { return ''; }
    public function renderMainTemplate(): string { return ''; }
    public function render(string $file): string
    {
        $escaper = $this->_escaper;
        $block = $this;
        $hyvaCsp = new class { public function registerInlineScript(): void {} };
        ob_start();
        try {
            include (getenv('WIDGETKIT_TEMPLATE_ROOT') ?: dirname(__DIR__, 2)) . '/' . $file;
            return ob_get_contents();
        } finally { ob_end_clean(); }
    }
    public static function fixture(string $widgetId): array
    {
        $xml = simplexml_load_file(dirname(__DIR__, 2) . '/etc/widget.xml');
        foreach ($xml->widget as $widget) {
            if ((string)$widget['id'] !== $widgetId) { continue; }
            $params = [];
            foreach ($widget->parameters->parameter as $p) {
                $key = (string)$p['name'];
                $value = (string)$p->value;
                if ((string)$p->attributes('xsi', true)['type'] === 'select') {
                    $value = (string)($p->options->option[0]['value'] ?? '');
                    foreach ($p->options->option as $option) {
                        if ((string)$option['selected'] === 'true') { $value = (string)$option['value']; }
                    }
                }
                if (str_starts_with($key, 'repeatable_')) {
                    $fields = (new ReflectionClass((string)$p->block['class']))->getDefaultProperties()['rows'];
                    $row = [];
                    foreach ($fields as $name => $field) {
                        $row[$name] = isset($field['options']) ? (string)array_key_first($field['options']) : '';
                    }
                    $value = [array_replace($row, ['title' => 'Title', 'title_tag' => 'h3',
                        'content' => '<p>Formatted <strong>content</strong></p>', 'use_card' => 'true',
                        'button' => 'Details', 'button_link' => 'https://example.test/', 'product' => '1'])];
                }
                $params[$key] = $value;
            }
            return $params;
        }
        throw new RuntimeException('Unknown widget fixture');
    }
}
