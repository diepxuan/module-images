<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 12:49:36
 */

namespace Diepxuan\Images\Backend\Block\Media;

use Magento\Backend\Block\Media\Uploader as OriginUploader;

/**
 * Adminhtml media library uploader.
 *
 * @api
 *
 * @since 100.0.2
 */
class Uploader extends OriginUploader
{
    /**
     * Initialize block.
     */
    protected function _construct(): void
    {
        parent::_construct();

        $this->setId($this->getId() . '_Uploader');

        $uploadUrl = $this->_urlBuilder->getUrl('adminhtml/*/upload');
        $this->getConfig()->setUrl($uploadUrl);
        $this->getConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png .svg .webp)'),
                    'files' => ['*.gif', '*.jpg', '*.png', '*.svg', '*.webp'],
                ],
                'media' => [
                    'label' => __('Media (.avi, .flv, .swf)'),
                    'files' => ['*.avi', '*.flv', '*.swf'],
                ],
                'all' => ['label' => __('All Files'), 'files' => ['*.*']],
            ]
        );
    }
}
