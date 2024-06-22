<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 15:59:35
 */

namespace Diepxuan\Images\Plugin\Catalog\Controller\Adminhtml\Product\Gallery;

class Upload
{
    /**
     * Get the set of allowed file extensions.
     *
     * @param mixed $allowedMimeTypes
     *
     * @return array
     */
    public function afterGetAllowedExtensions($allowedMimeTypes)
    {
        return array_merge(
            $allowedMimeTypes,
            ['svg', 'webp'],
        );
    }
}
