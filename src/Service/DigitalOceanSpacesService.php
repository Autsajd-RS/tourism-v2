<?php

namespace App\Service;

use App\Message\ImageDeleted;
use App\Message\ImageUploaded;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class DigitalOceanSpacesService
{
    public const PROFILES_BUCKET = 'profiles';
    public const DESTINATIONS_BUCKET = 'destinations';

    public const PROFILE_IMAGE_TYPE = 'profile_image';
    public const DESTINATION_IMAGE_TYPE = 'destination_image';

    public function __construct(
        private ImageOptimizer $imageOptimizer,
        private FileUploader $fileUploader,
        private MessageBusInterface $messageBus,
        private FilesystemOperator $cdnStorage
    )
    {
    }

    public function upload(UploadedFile $uploadedFile, string $fileType): ?string
    {
        $locationPrefix = $this->getLocationPrefix(fileType: $fileType);
        $imageType = $this->getImageType(fileType: $fileType);

        if (!$locationPrefix || !$imageType) {
            return null;
        }

        $filename = $this->fileUploader->upload(file: $uploadedFile);
        $this->imageOptimizer->resizeImage(filename: $filename, imageType: $imageType);

        $this->messageBus->dispatch(new ImageUploaded(filename: $filename, locationPrefix: $locationPrefix));

        return str_replace($this->fileUploader->getTargetDirectory() . '/', '', $filename);
    }

    public function syncUpload(UploadedFile $uploadedFile, string $fileType): ?string
    {
        $locationPrefix = $this->getLocationPrefix(fileType: $fileType);
        $imageType = $this->getImageType(fileType: $fileType);

        if (!$locationPrefix || !$imageType) {
            return null;
        }

        $filename = $this->fileUploader->upload(file: $uploadedFile);
        $this->imageOptimizer->resizeImage(filename: $filename, imageType: $imageType);

        try {
            $this->cdnStorage->write(
                location: $locationPrefix . str_replace($this->fileUploader->getTargetDirectory() . '/', '', $filename),
                contents: file_get_contents($filename),
                config: ['visibility' => 'public']
            );

            $this->fileUploader->remove($filename);

        } catch (FilesystemException) {
            return null;
        }

        return str_replace($this->fileUploader->getTargetDirectory() . '/', '', $filename);
    }

    public function delete(string $filename, string $fileType): void
    {
        $locationPrefix = $this->getLocationPrefix(fileType: $fileType);

        if (!$locationPrefix) {
            return;
        }

        $this->messageBus->dispatch(new ImageDeleted(filename: $filename, locationPrefix: $locationPrefix));
    }

    private function getLocationPrefix(string $fileType): ?string
    {
        $locationPrefix = null;
        if ($fileType === self::PROFILE_IMAGE_TYPE) {
            $locationPrefix = '/' . self::PROFILES_BUCKET . '/';
        }

        if ($fileType === self::DESTINATION_IMAGE_TYPE) {
            $locationPrefix = '/' . self::DESTINATIONS_BUCKET . '/';
        }

        if (!$locationPrefix) {
            return null;
        }

        return $locationPrefix;
    }

    private function getImageType(string $fileType): ?string
    {
        $imageType = null;
        if ($fileType === self::PROFILE_IMAGE_TYPE) {
            $imageType = self::PROFILE_IMAGE_TYPE;
        }

        if ($fileType === self::DESTINATION_IMAGE_TYPE) {
            $imageType = self::DESTINATION_IMAGE_TYPE;
        }

        if (!$imageType) {
            return null;
        }

        return $imageType;
    }
}