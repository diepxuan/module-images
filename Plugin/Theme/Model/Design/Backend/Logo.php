<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 07:11:28
 */

namespace Diepxuan\Images\Plugin\Theme\Model\Design\Backend;

use Diepxuan\Images\Model\Extension;

class Logo
{
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

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
            (array) $extensions,
            $this->extension->getAllowedExtensions(),
        );
    }
}
