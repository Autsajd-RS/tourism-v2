<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    public const PROFILE_IMAGE_MAX_WIDTH = 200;
    public const PROFILE_IMAGE_MAX_HEIGHT = 200;
    public const DEST_IMAGE_MAX_WIDTH = 400;
    public const DEST_IMAGE_MAX_HEIGHT = 300;

    private Imagine $imagine;

    public function __construct(
        private string $publicDir
    )
    {
        $this->imagine = new Imagine();
    }

    public function resizeImageLocal(string $filename, string $imageType): void
    {
        $this->resizeImage(
            filename: $this->publicDir . $filename,
            imageType: $imageType
        );
    }

    public function resizeImage(string $filename, string $imageType): void
    {
        [$iWidth, $iHeight] = getimagesize($filename);
        $ratio = $iWidth / $iHeight;

        if ($imageType === DigitalOceanSpacesService::PROFILE_IMAGE_TYPE) {
            $width = self::PROFILE_IMAGE_MAX_WIDTH;
            $height = self::PROFILE_IMAGE_MAX_HEIGHT;
        } else {
            $width = self::DEST_IMAGE_MAX_WIDTH;
            $height = self::DEST_IMAGE_MAX_HEIGHT;
        }

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $photo = $this->imagine->open($filename);
        $photo->resize(new Box($width, $height))->save($filename);
    }
}