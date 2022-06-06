<?php

namespace App\MessageHandler;

use App\Message\ImageDeleted;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImageDeletedHandler
{
    public function __construct(
        private FilesystemOperator $cdnStorage
    )
    {
    }

    public function __invoke(ImageDeleted $message)
    {
        try {
            $this->cdnStorage->delete($message->getLocationPrefix() . $message->getFilename());
        } catch (FilesystemException $e) {
        }
    }
}