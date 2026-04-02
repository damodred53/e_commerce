<?php

namespace App\Controller;

use App\Entity\Document;
use App\Service\DocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api')]
final class DocumentController extends AbstractController
{
    #[Route('/document/{id}/download', name: 'app_document_download', methods: ['GET'])]
    public function downloadDocument(Document $document, DocumentStorage $documentStorage): BinaryFileResponse
    {
        $storedName = $document->getStoredName();

        if ($storedName === null) {
            throw $this->createNotFoundException('Document introuvable.');
        }

        $absolutePath = $documentStorage->getAbsolutePath($storedName);

        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Fichier introuvable sur le disque.');
        }

        return $this->file(
            $absolutePath,
            $document->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
