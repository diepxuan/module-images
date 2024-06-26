<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2024-06-26 15:28:35
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

        if (!\in_array($extension, $this->getVectorExtensions(), true)) {
            return false;
        }

        if (!is_file($filePath)) {
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

    /**
     * File is a webp image.
     *
     * @param mixed $filePath
     *
     * @return bool
     */
    public function isWebpImage($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (empty($extension) && file_exists($filePath)) {
            $mimeType  = mime_content_type($filePath);
            $extension = str_replace('image/', '', $mimeType);
        }

        if (!\in_array($extension, $this->getWebExtensions(), true)) {
            return false;
        }
        if (!is_file($filePath)) {
            return false;
        }

        try {
            $fp = fopen(realpath($filePath), 'r');
            if (!$fp) {
                return false;
            }

            $data          = fread($fp, 90);
            $header_format = 'A4RIFF/' . // get n string
                'I1FILESIZE/' . // get integer (file size but not actual size)
                'A4WEBP/' . // get n string
                'A4VP/' . // get n string
                'A74chunk';
            $header = unpack($header_format, $data);

            if (!isset($header['RIFF']) || 'RIFF' !== strtoupper($header['RIFF'])) {
                return false;
            }
            if (!isset($header['WEBP']) || 'WEBP' !== strtoupper($header['WEBP'])) {
                return false;
            }
            if (!isset($header['VP']) || !str_contains(strtoupper($header['VP']), 'VP8')) {
                return false;
            }

            if (
                str_contains(strtoupper($header['chunk']), 'ANIM')
                || str_contains(strtoupper($header['chunk']), 'ANMF')
            ) {
                $header['ANIMATION'] = true;
            } else {
                $header['ANIMATION'] = false;
            }

            // check for transparent.
            if (str_contains(strtoupper($header['chunk']), 'ALPH')) {
                $header['ALPHA'] = true;
            } else {
                if (str_contains(strtoupper($header['VP']), 'VP8L')) {
                    // if it is VP8L.
                    // @link https://developers.google.com/speed/webp/docs/riff_container#simple_file_format_lossless Reference.
                    $header['ALPHA'] = (bool) ((bool) (\ord($data[24]) & 0x00_00_00_10));
                } elseif (str_contains(strtoupper($header['VP']), 'VP8X')) {
                    // if it is VP8X.
                    // @link https://developers.google.com/speed/webp/docs/riff_container#extended_file_format Reference.
                    // @link https://stackoverflow.com/a/61242086/128761 Original source code.
                    $header['ALPHA'] = (bool) ((bool) (\ord($data[20]) & 0x00_00_00_10));
                } else {
                    $header['ALPHA'] = false;
                }
            }

            // get width & height.
            // @link https://developer.wordpress.org/reference/functions/wp_get_webp_info/ Original source code.
            if ('VP8' === strtoupper($header['VP'])) {
                $parts            = unpack('v2', substr($data, 26, 4));
                $header['WIDTH']  = (int) ($parts[1] & 0x3F_FF);
                $header['HEIGHT'] = (int) ($parts[2] & 0x3F_FF);
            } elseif ('VP8L' === strtoupper($header['VP'])) {
                $parts            = unpack('C4', substr($data, 21, 4));
                $header['WIDTH']  = (int) (($parts[1] | (($parts[2] & 0x3F) << 8)) + 1);
                $header['HEIGHT'] = (int) (((($parts[2] & 0xC0) >> 6) | ($parts[3] << 2) | (($parts[4] & 0x03) << 10)) + 1);
            } elseif ('VP8X' === strtoupper($header['VP'])) {
                // Pad 24-bit int.
                $width           = unpack('V', substr($data, 24, 3) . "\x00");
                $header['WIDTH'] = (int) ($width[1] & 0xFF_FF_FF) + 1;
                // Pad 24-bit int.
                $height           = unpack('V', substr($data, 27, 3) . "\x00");
                $header['HEIGHT'] = (int) ($height[1] & 0xFF_FF_FF) + 1;
            }

            // return $header;

            return true;
        } catch (\Throwable $th) {
            // throw $th;

            return false;
        } finally {
            fclose($fp);
        }
    }

    /**
     * File is a Web image.
     *
     * @param mixed $filePath
     *
     * @return bool
     */
    public function isWebImage($filePath)
    {
        return $this->isWebpImage($filePath) || $this->isVectorImage($filePath);
    }
}
