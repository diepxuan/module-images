<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-26 16:34:08
 */

namespace Diepxuan\Images\Plugin\Controller\Adminhtml\Wysiwyg;

use Diepxuan\Images\Model\Extension;
use Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive as OriginDirective;
use Magento\Cms\Model\Template\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Url\DecoderInterface;

class Directive
{
    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @var null|Filesystem
     */
    private $filesystem;

    /**
     * DirectivePlugin constructor.
     */
    public function __construct(
        DecoderInterface $urlDecoder,
        Filter $filter,
        RawFactory $resultRawFactory,
        ?AdapterFactory $adapterFactory,
        Extension $extension,
        ?Filesystem $filesystem = null
    ) {
        $this->urlDecoder       = $urlDecoder;
        $this->filter           = $filter;
        $this->resultRawFactory = $resultRawFactory;
        $this->adapterFactory   = $adapterFactory ?: ObjectManager::getInstance()->get(AdapterFactory::class);
        $this->extension        = $extension;
        $this->filesystem       = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Handle vector images for media storage thumbnails.
     *
     * @return Raw
     */
    public function aroundExecute(OriginDirective $subject, callable $proceed)
    {
        try {
            $directive = $subject->getRequest()->getParam('___directive');
            $directive = $this->urlDecoder->decode($directive);

            /** @var Filter $filter */
            $imagePath = $this->filter->filter($directive);

            if (!$this->extension->isWebImage($imagePath)) {
                throw new LocalizedException(__('This image type is not a Web'));
            }

            /** @var AdapterInterface $image */
            $image = $this->adapterFactory->create();
            $image->open($imagePath);

            $mimeType = $image->getMimeType();
            $content  = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver()
                ->fileGetContents($imagePath)
            ;

            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', $mimeType);
            $resultRaw->setContents($content);

            return $resultRaw;
        } catch (\Exception $e) {
            return $proceed();
        }
    }
}
