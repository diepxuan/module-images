<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-26 22:08:58
 */

namespace Diepxuan\Images\Plugin\Catalog\Model\Product\Gallery;

use Diepxuan\Images\Model\Extension;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap as Origin;

class MimeTypeExtensionMap
{
    /**
     * @var Extension
     */
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

    /**
     * @param string $mimeType
     *
     * @return string
     */
    public function aroundGetMimeTypeExtension(Origin $subject, callable $proceed, $mimeType)
    {
        if (($extension = $this->extension->getAllowedMimeType($mimeType)) !== '') {
            return $extension;
        }

        return $proceed($mimeType);
    }
}
