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
 * @lastupdate 2024-06-27 12:11:41
 */

namespace Diepxuan\Images\Plugin\MediaGalleryRenditions\Model;

use Diepxuan\Images\Model\Extension;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryRenditions\Model\Config;
use Magento\MediaGalleryRenditions\Model\GenerateRenditions as Origin;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateRenditions
{
    private const IMAGE_FILE_NAME_PATTERN = '#\.(jpg|jpeg|gif|png|svg|webp)$# i';

    /**
     * @var File
     */
    private $driver;

    /**
     * @var GetRenditionPathInterface
     */
    private $getRenditionPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @var AdapterFactory
     */
    private $imageFactory;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    public function __construct(
        AdapterFactory $imageFactory,
        Config $config,
        GetRenditionPathInterface $getRenditionPath,
        Filesystem $filesystem,
        File $driver,
        IsPathExcludedInterface $isPathExcluded,
        Extension $extension
    ) {
        $this->imageFactory     = $imageFactory;
        $this->config           = $config;
        $this->getRenditionPath = $getRenditionPath;
        $this->filesystem       = $filesystem;
        $this->driver           = $driver;
        $this->isPathExcluded   = $isPathExcluded;
        $this->extension        = $extension;
    }

    /**
     * Handle web images for media gallery renditions.
     *
     * @return Raw
     */
    public function aroundExecute(Origin $subject, callable $proceed, array $paths)
    {
        $failedPaths = [];

        try {
            foreach ($paths as $path) {
                try {
                    if (!$this->extension->isWebImage($path)) {
                        throw new LocalizedException(__('This image type is not a Web'));
                    }
                    $this->generateRendition($path);
                } catch (\Exception $exception) {
                    $failedPaths[] = $path;
                }
            }
        } catch (\Exception $e) {
            return $proceed($failedPaths);
        }
    }

    /**
     * Generate rendition for media asset path.
     *
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws \Exception
     */
    private function generateRendition(string $path): void
    {
        $this->validateAsset($path);

        $renditionPath = $this->getRenditionPath->execute($path);
        $this->createDirectory($renditionPath);

        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);

        if ($this->shouldFileBeResized($absolutePath)) {
            $this->createResizedRendition(
                $absolutePath,
                $this->getMediaDirectory()->getAbsolutePath($renditionPath)
            );
        } else {
            $this->getMediaDirectory()->copyFile($path, $renditionPath);
        }
    }

    /**
     * Ensure valid media asset path is provided for renditions generation.
     *
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function validateAsset(string $path): void
    {
        if (!$this->getMediaDirectory()->isFile($path)) {
            throw new LocalizedException(__('Media asset file %path does not exist!', ['path' => $path]));
        }

        if ($this->isPathExcluded->execute($path)) {
            throw new LocalizedException(
                __('Could not create rendition for image, path is restricted: %path', ['path' => $path])
            );
        }

        if (!preg_match(self::IMAGE_FILE_NAME_PATTERN, $path)) {
            throw new LocalizedException(
                __('Could not create rendition for image, unsupported file type: %path.', ['path' => $path])
            );
        }
    }

    /**
     * Create directory for rendition file.
     *
     * @throws LocalizedException
     */
    private function createDirectory(string $path): void
    {
        try {
            $this->getMediaDirectory()->create($this->driver->getParentDirectory($path));
        } catch (\Exception $exception) {
            throw new LocalizedException(__('Cannot create directory for rendition %path', ['path' => $path]));
        }
    }

    /**
     * Create rendition file.
     *
     * @throws \Exception
     */
    private function createResizedRendition(string $absolutePath, string $absoluteRenditionPath): void
    {
        $image = $this->imageFactory->create();
        $image->open($absolutePath);
        $image->keepAspectRatio(true);
        $image->resize($this->config->getWidth(), $this->config->getHeight());
        $image->save($absoluteRenditionPath);
    }

    /**
     * Check if image needs to resize or not.
     */
    private function shouldFileBeResized(string $absolutePath): bool
    {
        [$width, $height] = getimagesizefromstring($this->getMediaDirectory()->readFile($absolutePath));

        return $width > $this->config->getWidth() || $height > $this->config->getHeight();
    }

    /**
     * Retrieve a media directory instance with write permissions.
     *
     * @throws FileSystemException
     */
    private function getMediaDirectory(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
