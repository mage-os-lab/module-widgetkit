<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Slider;

use MageOS\AdvancedWidget\Block\WidgetField\Rows;

class Item extends Rows
{
    protected $rows = [
        'image' => [
            'label' => 'Desktop Image',
            'type' => 'image',
            'description' => 'Image used on desktop, if mobile one is missing this image will be used for both viewports.',
            'required' => true,
            'preview' => true
        ],
        'image_alt' => [
            'label' => 'Desktop Image alt',
            'type' => 'text',
            'description' => 'Desktop image alternative text',
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
            'options' => [
                'h3' => 'H3',
                'h1' => 'H1',
                'h2' => 'H2',
                'h4' => 'H4',
                'h5' => 'H5',
                'p' => 'Paragraph',
                'span' => 'Span'
            ],
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
            'options' => [
                '_self' => 'Self',
                '_blank' => 'Blank'
            ],
            'required' => false,
            'preview' => false
        ],
        'navigation_thumb' => [
            'label' => 'navigation Thumb',
            'type' => 'image',
            'description' => 'If specified, thumb image will be used instead of resized desktop image',
            'required' => false,
            'preview' => false
        ],
    ];

}
