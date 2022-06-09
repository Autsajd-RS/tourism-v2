<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Entity\DestinationImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DestinationImageService
{
    public function __construct(
        private Crud $crud,
        private DigitalOceanSpacesService $digitalOceanSpacesService,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function addFromRequest(Request $request, Destination $destination): ErrorResponse|Destination
    {
        if (!$request->files->has('destinationPrimaryPhoto')) {
            return new ErrorResponse(
                message: 'Primary image upload failed',
                errors: ['image' => 'not found']
            );
        }

        $image = $request->files->get('destinationPrimaryPhoto');

        /*$filename = $this->digitalOceanSpacesService->upload(
            uploadedFile: $image,
            fileType: DigitalOceanSpacesService::DESTINATION_IMAGE_TYPE
        );*/

        $filename = $this->digitalOceanSpacesService->syncUpload(
            uploadedFile: $image,
            fileType: DigitalOceanSpacesService::DESTINATION_IMAGE_TYPE
        );

        $this->removeMainImageFlagFromExistingImage(destination: $destination);

        $destinationImage = (new DestinationImage())
            ->setName($filename)
            ->setDestination($destination)
            ->setMain(true);

        $this->crud->create($destinationImage);
        $this->crud->refresh($destination);

        return $destination;
    }

    public function removeMainImageFlagFromExistingImage(Destination $destination): void
    {
        foreach ($destination->getAdditionalImages() as $additionalImage) {
            if ($additionalImage->isMain()) {
                $additionalImage->setMain(false);

                $this->crud->patch($additionalImage);
            }
        }
    }

    public function deleteImage(int $imageId): void
    {
        $image = $this->entityManager->getRepository(DestinationImage::class)->find($imageId);

        if (!$image) {
            return;
        }

        $this->digitalOceanSpacesService->delete(filename: $image->getName(), fileType: DigitalOceanSpacesService::DESTINATION_IMAGE_TYPE);

        $this->crud->remove($image);
    }
}