<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Exception\LocalizedException;
use Magento\Widget\Block\BlockInterface;
use MageOS\AdvancedWidget\Block\Widgets\AbstractColumns;

class HyvaWidget extends AbstractColumns implements BlockInterface
{

    protected string $_twindManagerTemplate = 'MageOS_Widgetkit::widget/hyva/twind.phtml';


    /**
     * @param string $uid
     * @param string $widgetCssClass
     * @return string
     * @throws LocalizedException
     */
    public function addTwindManagement(string $uid, string $widgetCssClass): string
    {
        return $this->getLayout()->createBlock(
            self::class,
        )->setTemplate(
            $this->_twindManagerTemplate
        )
        ->setData(
            [
                'uid' => $uid,
                'cssClass' => $widgetCssClass
            ]
        )->toHtml();
    }
}
