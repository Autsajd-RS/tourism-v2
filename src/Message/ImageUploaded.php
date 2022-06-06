<?php

namespace App\Message;

class ImageUploaded
{
    private string $filename;

    private string $locationPrefix;

    public function __construct(
        string $filename,
        string $locationPrefix
    )
    {
        $this->filename = $filename;
        $this->locationPrefix = $locationPrefix;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getLocationPrefix(): string
    {
        return $this->locationPrefix;
    }
}