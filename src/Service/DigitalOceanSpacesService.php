<?php

namespace App\Service;

use App\Message\ImageDeleted;
use App\Message\ImageUploaded;
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
        private MessageBusInterface $messageBus
    )
    {
    }

    public function upload(UploadedFile $uploadedFile, string $fileType): ?string
    {
        $locationPrefix = $this->getLocationPrefix(fileType: $fileType);

        if (!$locationPrefix) {
            return null;
        }

        $filename = $this->fileUploader->upload(file: $uploadedFile);
        $this->imageOptimizer->resizeProfileImage($filename);

        $this->messageBus->dispatch(new ImageUploaded(filename: $filename, locationPrefix: $locationPrefix));

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
}