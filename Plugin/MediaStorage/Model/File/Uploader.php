<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 19:12:46
 */

namespace Diepxuan\Images\Plugin\MediaStorage\Model\File;

use Diepxuan\Images\Model\Extension;

class Uploader
{
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

    /**
     * Set allowed extensions.
     *
     * @param string[] $extensions
     *
     * @return $this
     */
    public function beforeSetAllowedExtensions(self $uploader, $extensions = [])
    {
        $extensions = array_merge(
            $extensions,
            $this->extension->getAllowedExtensions()
        );

        return [$extensions];
    }
}
