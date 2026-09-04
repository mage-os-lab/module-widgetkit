<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml;

use MageOS\Widgetkit\Model\PreviewStylesheet;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class PreviewStyles extends Template
{
    public function __construct(
        protected PreviewStylesheet $previewStylesheet,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getPreviewStylesheetUrl(): ?string
    {
        return $this->previewStylesheet->getUrl();
    }
}
