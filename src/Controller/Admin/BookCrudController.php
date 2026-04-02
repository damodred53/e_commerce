<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Entity\Document;
use App\Service\DocumentStorage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class BookCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DocumentStorage $documentStorage
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            MoneyField::new('price')->setCurrency('EUR'),
            IntegerField::new('quantity'),
            TextareaField::new('description'),
            TextField::new('imageUrl'),
            TextField::new('status'),
            TextField::new('author'),
            IntegerField::new('pages'),
            Field::new('documentFile', 'Document')
                ->setFormType(FileType::class)
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp('PDF ou TXT, 10 Mo max')
                ->setFormTypeOption('attr.accept', '.pdf,.txt,application/pdf,text/plain'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Book) {
            $this->handleDocumentUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Book) {
            $this->handleDocumentUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Book && $entityInstance->getDocument()?->getStoredName()) {
            $this->documentStorage->delete($entityInstance->getDocument()->getStoredName());
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    private function handleDocumentUpload(Book $book): void
    {
        $uploadedFile = $book->getDocumentFile();

        if ($uploadedFile === null) {
            return;
        }

        $existingDocument = $book->getDocument();

        if ($existingDocument !== null && $existingDocument->getStoredName() !== null) {
            $this->documentStorage->delete($existingDocument->getStoredName());
        }

        $storedFile = $this->documentStorage->store($uploadedFile);

        $document = $existingDocument ?? new Document();
        $document
            ->setOriginalName($storedFile['originalName'])
            ->setStoredName($storedFile['storedName'])
            ->setMimeType($storedFile['mimeType'])
            ->setSize($storedFile['size'])
            ->setPath($storedFile['path'])
            ->setCreatedAt(new \DateTimeImmutable());

        $book->setDocument($document);
        $book->setDocumentFile(null);
    }
}
