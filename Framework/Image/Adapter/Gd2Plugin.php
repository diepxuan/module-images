<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-04 00:23:25
 */

namespace Diepxuan\Images\Framework\Image\Adapter;

use Magento\Framework\Image\Adapter\Gd2;

/**
 * Gd2 adapter.
 *
 * Class is a copy of \Magento\Framework\Image\Adapter\Gd2
 * var $_callbacks add IMAGETYPE_WEBP
 * function _getTransparency IMAGETYPE_WEBP isTrueColor
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Gd2Plugin
{
    public function aroundValidateURLScheme(
        Gd2 $subject,
        \Closure $proceed,
        $filename
    ) {
        if ('svg' === pathinfo($filename, PATHINFO_EXTENSION)) {
            return true;
        }

        return $proceed($filename);
    }
}
