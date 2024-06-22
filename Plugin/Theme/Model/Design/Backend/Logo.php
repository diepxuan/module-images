<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 15:58:46
 */

namespace Diepxuan\Images\Plugin\Theme\Design\Backend;

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
