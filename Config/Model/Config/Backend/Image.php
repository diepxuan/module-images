<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-12-04 22:34:14
 */

namespace Diepxuan\Images\Config\Model\Config\Backend;

use Diepxuan\Images\Model\Extension;
use Magento\Config\Model\Config\Backend\Image as OriginImage;

class Image extends OriginImage
{
    private $extension;

    protected function getExtension(): Extension
    {
        $this->extension = $this->extension ?: new Extension();

        return $this->extension;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return array_merge(
            parent::_getAllowedExtensions(),
            $this->getExtension()->getAllowedExtensions(),
        );
    }
}
