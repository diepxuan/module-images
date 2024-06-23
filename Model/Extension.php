<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-23 10:33:42
 */

namespace Diepxuan\Images\Model;

class Extension
{
    /**
     * @var array
     */
    protected $webMimeTypes = [
        'webp' => 'image/webp',
    ];

    /**
     * @var array
     */
    protected $vectorMimeTypes = [
        'svg' => 'image/svg+xml',
    ];

    /**
     * @var array
     */
    protected $allowedMimeTypes = [];

    public function __construct(
    ) {
        $this->allowedMimeTypes = array_merge($this->webMimeTypes, $this->vectorMimeTypes);
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return array_keys($this->allowedMimeTypes);
    }

    public function getWebExtensions()
    {
        return array_keys($this->webMimeTypes);
    }

    public function getVectorExtensions()
    {
        return array_keys($this->vectorMimeTypes);
    }

    /**
     * File is a vector image.
     *
     * @param mixed $filePath
     *
     * @return bool
     */
    public function isVectorImage($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (empty($extension) && file_exists($filePath)) {
            $mimeType  = mime_content_type($filePath);
            $extension = str_replace('image/', '', $mimeType);
        }

        $allowed = \in_array($extension, $this->getVectorExtensions(), true);
        if (!$allowed) {
            return false;
        }

        try {
            $xmlReader = new \XMLReader();
            $xmlReader->open($filePath);
            if (\XMLReader::ELEMENT === $xmlReader->moveToElement() && 'svg' === strtolower($xmlReader->name)) {
                return true;
            }

            return false;
        } catch (\Throwable $th) {
            // throw $th;

            return false;
        } finally {
            $xmlReader->close();
        }
    }
}
