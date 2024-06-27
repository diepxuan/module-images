<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 19:07:46
 */

namespace Diepxuan\Images\Plugin\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content as OriginContent;
use Magento\Framework\View\Element\AbstractBlock;

class Content
{
    /**
     * Set layout object.
     *
     * @return $this
     */
    public function afterSetLayout(OriginContent $subject, AbstractBlock $block)
    {
        try {
            $block->getUploader()->getConfig()->setFilters(
                [
                    'images' => [
                        'label' => __('Images (.gif, .jpg, .png, .svg, .webp)'),
                        'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png', '*.svg' . '*.webp'],
                    ],
                ],
            );
        } catch (\Throwable $th) {
            // throw $th;
        } finally {
            return $block;
        }

        return $block;
    }
}
