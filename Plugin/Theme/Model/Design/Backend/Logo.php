<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 20:42:02
 */

namespace Diepxuan\Images\Plugin\Theme\Model\Design\Backend;

class Logo
{
    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @param mixed $extensions
     *
     * @return string[]
     */
    public function afterGetAllowedExtensions($extensions)
    {
        return array_merge(
            $extensions,
            ['svg', 'webp'],
        );
    }
}
