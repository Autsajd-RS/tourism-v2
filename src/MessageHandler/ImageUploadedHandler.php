<?php

namespace App\MessageHandler;

use App\Message\ImageUploaded;
use App\Service\FileUploader;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImageUploadedHandler
{
    public function __construct(
        private FileUploader $fileUploader,
        private FilesystemOperator $cdnStorage
    )
    {
    }

    public function __invoke(ImageUploaded $message)
    {
        try {
            $this->cdnStorage->write(
                location: $this->resolveLocation(message: $message),
                contents: file_get_contents($message->getFilename()),
                config: ['visibility' => 'public']
            );

            $this->fileUploader->remove($message->getFilename());

        } catch (FilesystemException $e) {
        }
    }

    private function resolveLocation(ImageUploaded $message): string
    {
        return $message->getLocationPrefix() . str_replace($this->fileUploader->getTargetDirectory() . '/', '', $message->getFilename());
    }
}