<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\WidgetField;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;

/**
 * Renders a top-level widget.xml `xsi:type="block"` parameter as a native admin
 * date + time picker (Magento's standard jQuery UI calendar element).
 *
 * The date/time format is fixed (not locale-derived) so the value can be parsed
 * back reliably with `\DateTime::createFromFormat()` on the frontend without any
 * ambiguity between day/month ordering.
 */
class DateTime extends Template
{
    public const DATE_FORMAT = 'MM/dd/yyyy';
    public const TIME_FORMAT = 'HH:mm';
    public const PHP_DATETIME_FORMAT = 'm/d/Y H:i';

    public function __construct(
        Context $context,
        private readonly Factory $elementFactory,
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
        $input = $this->elementFactory->create('date', ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass('widget-option input-text admin__control-text input-date');
        $input->setDateFormat(self::DATE_FORMAT);
        $input->setTimeFormat(self::TIME_FORMAT);
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $this->_getAfterElementHtml() . $input->getElementHtml());

        return $element;
    }

    /**
     * @return string
     */
    protected function _getAfterElementHtml(): string
    {
        return <<<HTML
            <style>
                .admin__field-control.control .control-value {
                    display: none !important;
                }
            </style>
        HTML;
    }
}
