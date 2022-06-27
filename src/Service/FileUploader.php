<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{

    public function __construct(
        private string $tmpDirectory,
        private SluggerInterface $slugger,
        private Filesystem $filesystem,
        private string $publicDir
    )
    {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid('', true) . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $this->getTargetDirectory() . '/' . $fileName;
    }

    public function uploadPublic(UploadedFile $file, string $location): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid('', true) . '.' . $file->guessExtension();

        try {
            $file->move($this->publicDir . $location, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $location . '/' . $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->tmpDirectory;
    }

    public function remove(string $filename): void
    {
        $this->filesystem->remove($filename);
    }

    public function removeFromPublic(string $filename): void
    {
        $this->filesystem->remove($this->publicDir . $filename);
    }
}