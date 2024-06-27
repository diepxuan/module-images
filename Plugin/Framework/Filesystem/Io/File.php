<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-27 15:55:02
 */

namespace Diepxuan\Images\Plugin\Framework\Filesystem\Io;

use Diepxuan\Images\Model\Extension;
use Magento\Framework\Filesystem\Io\File as OriginFile;

class File
{
    private $extension;

    public function __construct(
        Extension $extension
    ) {
        $this->extension = $extension;
    }

    /**
     * Get list of cwd subdirectories and files.
     *
     * Suggestions (from moshe):
     * - Use filemtime instead of filectime for performance
     * - Change $grep to $flags and use binary flags
     *   - LS_DIRS  = 1
     *   - LS_FILES = 2
     *   - LS_ALL   = 3
     *
     * @param mixed $list
     *
     * @return array
     *
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function afterLs(OriginFile $subject, $list = [])
    {
        try {
            $list = array_map(function ($listItem) {
                $fullPath = $listItem['id'];
                $pathInfo = pathinfo($fullPath);

                if ($this->extension->isWebImage($fullPath)) {
                    $listItem['is_image'] = true;
                    $listItem['filetype'] = $pathInfo['extension'];
                }

                return $listItem;
            }, $list);
        } catch (\Throwable $th) {
            // throw $th;
        } finally {
            return $list;
        }
    }
}
