<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 14:32:21
 */

namespace Diepxuan\Images\Catalog\Model\ResourceModel\Product\Attribute\Backend;

use Diepxuan\Images\Model\Extension;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Image as OriginImage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Image extends OriginImage
{
    /**
     * Filesystem facade.
     *
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory.
     *
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var Extension
     */
    private $extension;

    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        Extension $extension
    ) {
        parent::__construct($filesystem, $fileUploaderFactory);
        $this->extension = $extension;
    }

    /**
     * After save.
     *
     * @param DataObject $object
     *
     * @return $this|void
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (\is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());

            return;
        }

        try {
            /** @var Uploader $uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $this->getAttribute()->getName()]);
            $uploader->setAllowedExtensions(array_merge(['jpg', 'jpeg', 'gif', 'png'], $this->extension->getAllowedExtensions()));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
        } catch (\Exception $e) {
            return $this;
        }
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'catalog/product/'
        );
        $uploader->save($path);

        $fileName = $uploader->getUploadedFileName();
        if ($fileName) {
            $object->setData($this->getAttribute()->getName(), $fileName);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        }

        return $this;
    }
}
