<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Grid;

use MageOS\AdvancedWidget\Block\WidgetField\Rows;

class Item extends Rows
{
    protected $rows = [
        'image' => [
            'label' => 'Desktop Image',
            'type' => 'image',
            'description' => 'Image used on desktop, if mobile one is missing this image will be used for both viewports.',
            'required' => false,
            'preview' => true
        ],
        'image_alt' => [
            'label' => 'Alt Image',
            'type' => 'text',
            'description' => 'Image alternative text',
            'required' => false,
            'preview' => false
        ],
        'mobile_image' => [
            'label' => 'Mobile Image',
            'type' => 'image',
            'description' => 'Image used on mobile.',
            'required' => false,
            'preview' => false
        ],
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
            'type' => 'textarea',
            'required' => false,
            'preview' => true
        ],
        'button' => [
            'label' => 'Button',
            'type' => 'text',
            'description' => 'button text, no button will appear if not specified.',
            'required' => false,
            'preview' => false,
        ],
        'button_link' => [
            'label' => 'Button link',
            'type' => 'text',
            'required' => false,
            'preview' => false
        ],
        'button_link_target' => [
            'label' => 'Button link target',
            'type' => 'select',
            'options' => ['_self' => 'Self', '_blank' => 'Blank'],
            'required' => false,
            'preview' => false
        ],
        'use_card' => [
            'label' => 'Use card around item',
            'type' => 'select',
            'options' => [
                'true' => 'Yes',
                'false' => 'No'
            ],
            'required' => false,
            'preview' => false
        ],
        'col_start_desktop' => [
            'label' => 'Column start on desktop viewport (>= 1024px)',
            'type' => 'select',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
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
        ],
        'col_end_desktop' => [
            'label' => 'Column end on desktop viewport (>= 1024px)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'lg:col-end-1' => 'End at first column (lg:end-start-1)',
                'lg:col-end-2' => 'End at second column (lg:end-start-2)',
                'lg:col-end-3' => 'End at third column (lg:end-start-3)',
                'lg:col-end-4' => 'End at fourth column (lg:end-start-4)',
                'lg:col-end-5' => 'End at fifth column (lg:end-start-5)',
                'lg:col-end-6' => 'End at fifth column (lg:end-start-6)',
            ],
            'required' => false,
            'preview' => false
        ],
        'col_end_tablet' => [
            'label' => 'Column end on tablet viewport (> 768px <= 1024px)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'md:col-end-1' => 'End at first column (md:col-end-1)',
                'md:col-end-2' => 'End at second column (md:col-end-2)',
                'md:col-end-3' => 'End at third column (md:col-end-3)',
                'md:col-end-4' => 'End at fourth column (md:col-end-4)',
                'md:col-end-5' => 'End at fifth column (md:col-end-5)',
                'md:col-end-6' => 'End at fifth column (md:col-end-6)',
            ],
            'required' => false,
            'preview' => false
        ],
        'col_end_global' => [
            'label' => 'Column end on all viewports (default)',
            'description' => 'See tailwind grid-column documentation <a href="https://tailwindcss.com/docs/grid-column#starting-and-ending-lines" target="_blank"/>here</a>.',
            'type' => 'select',
            'options' => [
                'auto' => 'Auto',
                'col-end-1' => 'End at first column (col-end-1)',
                'col-end-2' => 'End at second column (col-end-2)',
                'col-end-3' => 'End at third column (col-end-3)',
                'col-end-4' => 'End at fourth column (col-end-4)',
                'col-end-5' => 'End at fifth column (col-end-5)',
                'col-end-6' => 'End at fifth column (col-end-6)',
            ],
            'required' => false,
            'preview' => false
        ]
    ];
}
