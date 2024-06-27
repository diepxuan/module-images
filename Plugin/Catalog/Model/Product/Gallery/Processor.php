<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 14:49:59
 */

namespace Diepxuan\Images\Plugin\Catalog\Model\Product\Gallery;

use Diepxuan\Images\Model\Extension;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap as Origin;
use Magento\Catalog\Model\Product\Gallery\Processor as OriginProcessor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;

class Processor extends OriginProcessor
{ /**
     * @var Mime
    */
    private $mime;

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @throws FileSystemException
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        Database $fileStorageDb,
        Config $mediaConfig,
        Filesystem $filesystem,
        Gallery $resourceModel,
        ?Mime $mime,
        Extension $extension
    ) {
        $this->mime = $mime ?: ObjectManager::getInstance()->get(Mime::class);
        parent::__construct(
            $attributeRepository,
            $fileStorageDb,
            $mediaConfig,
            $filesystem,
            $resourceModel,
            $this->mime
        );
        $this->extension = $extension;
    }

    public function aroundAddImage(
        Origin $subject,
        callable $proceed,
        Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ) {
        try {
            $fileName = $this->_addImage(
                $product,
                $file,
                $mediaAttribute,
                $move,
                $exclude
            );
            if (!$fileName) {
                throw new LocalizedException(__("The image doesn't exist."));
            }

            return $fileName;
        } catch (\Throwable $th) {
            // throw $th;
            return $proceed(
                $product,
                $file,
                $mediaAttribute,
                $move,
                $exclude
            );
        }
    }

    /**
     * Add image to media gallery and return new filename.
     *
     * @param string          $file           file path of image in file system
     * @param string|string[] $mediaAttribute code of attribute with type 'media_image',
     *                                        leave blank if image should be only in gallery
     * @param bool            $move           if true, it will move source file
     * @param bool            $exclude        mark image as disabled in product page view
     *
     * @return string
     *
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @since 101.0.0
     */
    private function _addImage(
        Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ) {
        $file = $this->mediaDirectory->getRelativePath($file);
        if (!$this->mediaDirectory->isFile($file)) {
            throw new LocalizedException(__("The image doesn't exist."));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $pathinfo      = pathinfo($file);
        $imgExtensions = array_merge(['jpg', 'jpeg', 'gif', 'png'], $this->extension->getAllowedExtensions());
        if (!isset($pathinfo['extension']) || !\in_array(strtolower($pathinfo['extension']), $imgExtensions, true)) {
            throw new LocalizedException(
                __('The image type for the file is invalid. Enter the correct image type and try again.')
            );
        }

        $fileName       = Uploader::getCorrectFileName($pathinfo['basename']);
        $dispersionPath = Uploader::getDispersionPath($fileName);
        $fileName       = $dispersionPath . '/' . $fileName;

        $fileName = $this->getNotDuplicatedFilename($fileName, $dispersionPath);

        $destinationFile = $this->mediaConfig->getTmpMediaPath($fileName);

        try {
            /** @var Database $storageHelper */
            $storageHelper = $this->fileStorageDb;
            if ($move) {
                $this->mediaDirectory->renameFile($file, $destinationFile);

                // If this is used, filesystem should be configured properly
                $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
            } else {
                $this->mediaDirectory->copyFile($file, $destinationFile);

                $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The "%1" file couldn\'t be moved.', $e->getMessage()));
        }

        $fileName = str_replace('\\', '/', $fileName);

        $attrCode         = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position         = 0;

        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($destinationFile);
        $imageMimeType    = $this->mime->getMimeType($absoluteFilePath);
        $imageContent     = $this->mediaDirectory->readFile($absoluteFilePath);
        $imageBase64      = base64_encode($imageContent);
        $imageName        = $pathinfo['filename'];

        if (!\is_array($mediaGalleryData)) {
            $mediaGalleryData = ['images' => []];
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if (isset($image['position']) && $image['position'] > $position) {
                $position = $image['position'];
            }
        }

        ++$position;
        $mediaGalleryData['images'][] = [
            'file'       => $fileName,
            'position'   => $position,
            'label'      => '',
            'disabled'   => (int) $exclude,
            'media_type' => 'image',
            'types'      => $mediaAttribute,
            'content'    => [
                'data' => [
                    ImageContentInterface::NAME                => $imageName,
                    ImageContentInterface::BASE64_ENCODED_DATA => $imageBase64,
                    ImageContentInterface::TYPE                => $imageMimeType,
                ],
            ],
        ];

        $product->setData($attrCode, $mediaGalleryData);

        if (null !== $mediaAttribute) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }
}
