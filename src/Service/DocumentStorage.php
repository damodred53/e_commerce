<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentStorage
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly SluggerInterface $slugger
    ){}

    public function store(UploadedFile $file): array
    {

        $allowedMimeTypes = ['application/pdf','text/plain'];

        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid file type.');
        }

        $maxsize = 10 * 1024 *1024; // 10MB
        $size = $file->getSize();

        if ($size === false || $size === null) {
            throw new \RuntimeException('Unable to read uploaded file size.');
        }

        if ($file->getSize() > $maxsize) {
            throw new \InvalidArgumentException('File size exceeds the maximum allowed size of 10MB.');
        }

        if (!is_dir($this->targetDirectory) && !mkdir($concurrentDirectory = $this->targetDirectory, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException('Unable to create document storage directory.');
        }

        $originalFileName = pathInfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFileName);
        $extension = $file->guessExtension();

        if(!$extension) {
            $extension = match ($mimeType) {
                'application/pdf' => 'pdf',
                'text/plain' => 'txt',
                default => throw new \InvalidArgumentException('Unsupported file type.')
            };
        }

        $storedName = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $extension);

        try {
            $file->move($this->targetDirectory, $storedName);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to store the file.', 0, $e);
        }

        return [
            'originalName'  => $file->getClientOriginalName(),
            'storedName' =>$storedName,
            'mimeType' => $mimeType,
            'size' => $size,
            'path' => $storedName
        ];

    }

    public function delete(string $storedName): void
    {
        $absolutePath = $this->getAbsolutePath($storedName);

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    public function getAbsolutePath(string $storedName): string
    {
        return rtrim($this->targetDirectory, '/').'/'.$storedName;
    }
}
