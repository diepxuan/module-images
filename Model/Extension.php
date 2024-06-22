<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 18:56:17
 */

namespace Diepxuan\Images\Model;

class Extension
{
    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
    ];

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return array_keys($this->allowedMimeTypes);
    }
}
