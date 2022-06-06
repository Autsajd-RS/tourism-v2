<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    public const PROFILE_IMAGE_MAX_WIDTH = 200;
    public const PROFILE_IMAGE_MAX_HEIGHT = 200;

    private Imagine $imagine;

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    public function resizeProfileImage(string $filename): void
    {
        [$iWidth, $iHeight] = getimagesize($filename);
        $ratio = $iWidth / $iHeight;
        $width = self::PROFILE_IMAGE_MAX_WIDTH;
        $height = self::PROFILE_IMAGE_MAX_HEIGHT;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $photo = $this->imagine->open($filename);
        $photo->resize(new Box($width, $height))->save($filename);
    }
}