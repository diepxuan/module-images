<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-23 15:23:21
 */

namespace Diepxuan\Images\Framework\Image\Adapter;

use Diepxuan\Images\Model\Extension;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Image abstract adapter.
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractAdapter extends \Magento\Framework\Image\Adapter\AbstractAdapter
{
    /**
     * @var Extension
     */
    protected $extension;

    /**
     * Initialize default values.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        Extension $extension,
        array $data = [],
    ) {
        $this->_filesystem    = $filesystem;
        $this->logger         = $logger;
        $this->directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->extension      = $extension;
    }

    /**
     * Open image for processing.
     *
     * @param string $fileName
     */
    abstract public function open($fileName): void;

    /**
     * Save image to specific path.
     *
     * If some folders of the path do not exist they will be created.
     *
     * @param null|string $destination
     * @param null|string $newName
     *
     * @throws \Exception If destination path is not writable
     */
    abstract public function save($destination = null, $newName = null): void;

    /**
     * Render image and return its binary contents.
     *
     * @return string
     */
    abstract public function getImage();

    /**
     * Change the image size.
     *
     * @param null|int $width
     * @param null|int $height
     */
    abstract public function resize($width = null, $height = null): void;

    /**
     * Rotate image on specific angle.
     *
     * @param int $angle
     */
    abstract public function rotate($angle): void;

    /**
     * Crop image.
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     *
     * @return bool
     */
    abstract public function crop($top = 0, $left = 0, $right = 0, $bottom = 0);

    /**
     * Add watermark to image.
     *
     * @param string $imagePath
     * @param int    $positionX
     * @param int    $positionY
     * @param int    $opacity
     * @param bool   $tile
     */
    abstract public function watermark($imagePath, $positionX = 0, $positionY = 0, $opacity = 30, $tile = false): void;

    /**
     * Checks required dependencies.
     *
     * @throws \Exception If some of dependencies are missing
     */
    abstract public function checkDependencies(): void;

    /**
     * Create Image from string.
     *
     * @param string $text
     * @param string $font Path to font file
     *
     * @return AbstractAdapter
     */
    abstract public function createPngFromString($text, $font = '');

    /**
     * Reassign image dimensions.
     */
    abstract public function refreshImageDimensions(): void;

    /**
     * Returns rgba array of the specified pixel.
     *
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    abstract public function getColorAt($x, $y);

    /**
     * Return supported image formats.
     *
     * @return string[]
     */
    public function getSupportedFormats()
    {
        return array_merge(parent::getSupportedFormats(), $this->extension->getAllowedExtensions());
    }

    /**
     * Check - is this file an image.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function validateUploadFile($filePath)
    {
        return $this->extension->isVectorImage($filePath) || parent::validateUploadFile($filePath);
    }

    /**
     * Adapt resize values based on image configuration.
     *
     * @param int $frameWidth
     * @param int $frameHeight
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function _adaptResizeValues($frameWidth, $frameHeight)
    {
        $dims = parent::_adaptResizeValues($frameWidth, $frameHeight);
        foreach ($dims as $dimKey => $dim) {
            foreach ($dim as $sizeKey => $size) {
                $dims[$dimKey][$sizeKey] = (int) $size;
            }
        }
    }
}
