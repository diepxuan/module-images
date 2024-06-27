<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 15:17:22
 */

namespace Diepxuan\Images\Plugin\MediaGalleryUi\Ui\Component;

use Diepxuan\Images\Model\Extension;
use Magento\MediaGalleryUi\Ui\Component\ImageUploader as OriginImageUploader;

class ImageUploader
{
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

    public function afterPrepare(OriginImageUploader $uploader): void
    {
        $uploader->setData(
            'config',
            array_replace_recursive(
                (array) $uploader->getData('config'),
                [
                    'acceptFileTypes'   => $this->extension->getAllowedExtensionsRegex(),
                    'allowedExtensions' => $this->extension->getAllowedExtensionsString(),
                ]
            )
        );
    }
}
