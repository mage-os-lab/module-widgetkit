<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Accordion;

use MageOS\AdvancedWidget\Block\WidgetField\Rows;

class Item extends Rows
{
    protected $rows = [
        'title' => [
            'label' => 'Title',
            'type' => 'text',
            'required' => false,
            'preview' => true
        ],
        'title_tag' => [
            'label' => 'Title tag',
            'type' => 'select',
            'options' => ['h3' => 'H3', 'h1' => 'H1', 'h2' => 'H2', 'h4' => 'H4', 'h5' => 'H5', 'p' => 'Paragraph', 'span' => 'Span'],
            'required' => false,
            'preview' => false
        ],
        'content' => [
            'label' => 'Content',
            'type' => 'wysiwyg',
            'required' => false,
            'preview' => false
        ],
    ];
}
