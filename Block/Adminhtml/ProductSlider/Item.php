<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\ProductSlider;

use MageOS\AdvancedWidget\Block\WidgetField\Rows;

class Item extends Rows
{
    protected $rows = [
        'product' => [
            'label'       => 'Product',
            'type'        => 'product',
            'required'    => true,
            'preview'     => true,
        ]
    ];
}
