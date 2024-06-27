<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 16:08:48
 */

namespace Diepxuan\Images\Framework\Api;

use Diepxuan\Images\Model\Extension;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessor as OriginImageProcessor;
use Magento\Framework\Api\Uploader;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageProcessor extends OriginImageProcessor
{
    /**
     * @var Extension
     */
    protected $extension;

    public function __construct(
        Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        DataObjectHelper $dataObjectHelper,
        LoggerInterface $logger,
        Uploader $uploader,
        Extension $extension
    ) {
        parent::__construct(
            $fileSystem,
            $contentValidator,
            $dataObjectHelper,
            $logger,
            $uploader
        );
        $this->extension = $extension;
    }

    /**
     * Get mime type extension.
     *
     * @param string $mimeType
     *
     * @return string
     */
    protected function getMimeTypeExtension($mimeType)
    {
        return $this->extension->getAllowedMimeType($mimeType) ?? '';
    }
}
