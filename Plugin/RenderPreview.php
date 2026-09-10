<?php
declare(strict_types=1);
namespace MageOS\Widgetkit\Plugin;

use MageOS\Widgetkit\Block\Adminhtml\PreviewInterface;
use MageOS\Widgetkit\Model\PreviewEnvironment;
use MageOS\Widgetkit\Model\PreviewStylesheet;
use Magento\Framework\Escaper;

class RenderPreview
{
    public function __construct(
        private PreviewEnvironment $environment,
        private PreviewStylesheet $stylesheet,
        private Escaper $escaper
    ) {
    }

    public function aroundToHtml(PreviewInterface $subject, callable $proceed): string
    {
        if ($this->environment->isActive()) { return $proceed(); }
        return $this->environment->render(function () use ($proceed): string {
            $html = $proceed();
            $url = $this->stylesheet->getUrl();
            $styles = $url ? '<link rel="stylesheet" href="' . $this->escaper->escapeUrl($url) . '">' : '';
            return $styles . '<div data-widgetkit-preview="'
                . $this->escaper->escapeHtmlAttr($this->stylesheet->getScopeId()) . '">' . $html . '</div>';
        });
    }
}
