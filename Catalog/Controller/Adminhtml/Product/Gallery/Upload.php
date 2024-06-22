<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-22 17:29:10
 */

namespace Diepxuan\Images\Catalog\Controller\Adminhtml\Product\Gallery;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\Uploader;

class Upload extends \Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload
{
    /**
     * @var array
     */
    private $allowedMimeTypes = [
        'jpg'  => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
    ];

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $productMediaConfig;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        ?AdapterFactory $adapterFactory = null,
        ?Filesystem $filesystem = null,
        ?Config $productMediaConfig = null
    ) {
        $this->resultRawFactory   = $resultRawFactory;
        $this->adapterFactory     = $adapterFactory ?: ObjectManager::getInstance()->get(AdapterFactory::class);
        $this->filesystem         = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        $this->productMediaConfig = $productMediaConfig ?: ObjectManager::getInstance()->get(Config::class);
        parent::__construct(
            $context,
            $this->resultRawFactory,
            $this->adapterFactory,
            $this->filesystem,
            $this->productMediaConfig
        );
    }

    /**
     * Upload image(s) to the product gallery.
     *
     * @return Raw
     */
    public function execute()
    {
        try {
            $uploader = $this->_objectManager->create(
                Uploader::class,
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result         = $uploader->save(
                $mediaDirectory->getAbsolutePath($this->productMediaConfig->getBaseTmpMediaPath())
            );
            $this->_eventManager->dispatch(
                'catalog_product_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            if (\is_array($result)) {
                unset($result['tmp_name'], $result['path']);

                $result['url'] = $this->productMediaConfig->getTmpMediaUrl($result['file']);
                $result['file'] .= '.tmp';
            } else {
                $result = ['error' => 'Something went wrong while saving the file(s).'];
            }
        } catch (LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Throwable $e) {
            dd($e);
            $result = ['error' => 'Something went wrong while saving the file(s).', 'errorcode' => 0];
        }

        /** @var Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }

    /**
     * Get the set of allowed file extensions.
     *
     * @return array
     */
    private function getAllowedExtensions()
    {
        return array_keys($this->allowedMimeTypes);
    }
}
