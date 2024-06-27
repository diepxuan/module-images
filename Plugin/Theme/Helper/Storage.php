<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 14:58:49
 */

namespace Diepxuan\Images\Plugin\MediaStorage\Model\File;

use Diepxuan\Images\Model\Extension;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Helper\Storage as OriginStorage;
use Magento\Theme\Model\Wysiwyg\Storage as WysiwygStorage;

class Storage extends OriginStorage
{
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

    /**
     * Get allowed extensions by type.
     *
     * @return string[]
     *
     * @throws LocalizedException
     */
    public function aroundGetAllowedExtensionsByType(OriginStorage $subject, callable $proceed)
    {
        try {
            return WysiwygStorage::TYPE_FONT === $this->getStorageType()
                ? ['ttf', 'otf', 'eot', 'svg', 'woff']
                : array_merge(['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'], $this->extension->getAllowedExtensions());
        } catch (\Throwable $th) {
            // throw $th;

            return $proceed();
        }
    }
}
