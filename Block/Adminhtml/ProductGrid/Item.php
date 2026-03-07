<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\ProductGrid;

use MageOS\AdvancedWidget\Block\WidgetField\Rows;

class Item extends Rows
{
    protected $rows = [
        'product' => [
            'label'       => 'Product',
            'type'        => 'product',
            'required'    => true,
            'preview'     => true,
        ],
        'col_start_desktop' => [
            'label' => 'Column start on desktop viewport (>= 1024px)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'lg:col-start-1' => 'Start at first column (lg:col-start-1)',
                'lg:col-start-2' => 'Start at second column (lg:col-start-2)',
                'lg:col-start-3' => 'Start at third column (lg:col-start-3)',
                'lg:col-start-4' => 'Start at fourth column (lg:col-start-4)',
                'lg:col-start-5' => 'Start at fifth column (lg:col-start-5)',
                'lg:col-start-6' => 'Start at fifth column (lg:col-start-6)',
            ],
            'required' => false,
            'preview' => false
        ],
        'col_start_tablet' => [
            'label' => 'Column start on tablet viewport (> 768px <= 1024px)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'md:col-start-1' => 'Start at first column (md:col-start-1)',
                'md:col-start-2' => 'Start at second column (md:col-start-2)',
                'md:col-start-3' => 'Start at third column (md:col-start-3)',
                'md:col-start-4' => 'Start at fourth column (md:col-start-4)',
                'md:col-start-5' => 'Start at fifth column (md:col-start-5)',
                'md:col-start-6' => 'Start at fifth column (md:col-start-6)',
            ],
            'required' => false,
            'preview' => false
        ],
        'col_start_global' => [
            'label' => 'Column start on all viewports (default)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'col-start-1' => 'Start at first column (col-start-1)',
                'col-start-2' => 'Start at second column (col-start-2)',
                'col-start-3' => 'Start at third column (col-start-3)',
                'col-start-4' => 'Start at fourth column (col-start-4)',
                'col-start-5' => 'Start at fifth column (col-start-5)',
                'col-start-6' => 'Start at fifth column (col-start-6)',
            ],
            'required' => false,
            'preview' => false
        ]
    ];
}
