<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Banner;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use MageOS\Widgetkit\Model\Config\Source\LucideIcon;

/**
 * Renders the icon field as a real <select> populated from LucideIcon::toOptionArray(),
 * via xsi:type="block" instead of xsi:type="select" source_model="...".
 *
 * Deliberately NOT a plain `source_model` select: MageOS_PageBuilderWidget's
 * Build::sanitizeWidgetParams() only matches a submitted value against the STATIC
 * <options> declared in widget.xml (empty here, since the whole point of source_model
 * is generating ~1900 options in PHP instead of hardcoding them in XML) - when that
 * static list doesn't contain the value, it re-fetches the source_model's options but
 * then unconditionally overwrites the submitted value with options[0] regardless of
 * whether it's actually in that freshly-fetched list, silently discarding whatever
 * icon was picked. Rendering via xsi:type="block" instead hits sanitizeWidgetParams()'s
 * generic (non-'select') branch, which stores the submitted string as-is.
 */
class IconField extends Template
{
    public function __construct(
        Context $context,
        private readonly Factory $elementFactory,
        private readonly LucideIcon $lucideIcon,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $select = $this->elementFactory->create('select', ['data' => $element->getData()]);
        $select->setId($element->getId());
        $select->setForm($element->getForm());
        $select->setClass('widget-option select admin__control-select');
        $select->setValues($this->lucideIcon->toOptionArray());
        if ($element->getRequired()) {
            $select->addClass('required-entry');
        }

        $element->setData('after_element_html', $select->getElementHtml());

        return $element;
    }
}
