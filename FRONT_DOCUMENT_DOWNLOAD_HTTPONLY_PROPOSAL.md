# Analyse du systeme de fichiers et proposition front React avec authentification HTTP-only

Ce document analyse le systeme actuel de gestion des documents dans le projet, puis propose une solution backend + front React pour telecharger la fiche technique d'un produit avec un simple bouton.

L'objectif retenu est le suivant :

- chaque produit peut avoir un document de fiche technique
- le document est stocke physiquement dans `var/storage/documents`
- le front React ne manipule pas le token JWT directement
- l'authentification se fait par cookie HTTP-only
- le bouton React doit telecharger le bon PDF ou le bon fichier texte lie au produit

## 1. Analyse du systeme actuel

### 1.1 Stockage physique du fichier

Le stockage disque est gere par `src/Service/DocumentStorage.php`.

Points verifies :

- les formats acceptes sont `application/pdf` et `text/plain`
- la taille maximale est de 10 Mo
- le fichier est deplace dans le dossier configure par `document_storage_directory`
- ce dossier vaut `%kernel.project_dir%/var/storage/documents`

Le fichier reel televerse est donc stocke dans :

`var/storage/documents`

Le nom de fichier sauvegarde sur le disque n'est pas le nom original. Un nom technique est genere :

```php
$storedName = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $extension);
```

Donc un fichier du type `fiche-technique.pdf` peut devenir :

`fiche-technique-67e5d9d9d2f0f4.12345678.pdf`

### 1.2 Stockage en base de donnees

L'entite `src/Entity/Document.php` ne stocke pas le contenu du fichier. Elle stocke uniquement ses metadonnees :

- `originalName`
- `storedName`
- `mimeType`
- `size`
- `path`
- `createdAt`

Donc la base de donnees sert a faire le lien entre un produit et un fichier physique, mais le fichier reste sur disque.

### 1.3 Lien entre produit et document

Le lien est defini ici :

- `src/Entity/AbstractProduct.php`
- `src/Entity/Document.php`

Le modele actuel est un `OneToOne` bidirectionnel :

- un `Book` ou un `Movie` peut avoir un document
- un document est lie a un seul produit

En pratique, c'est coherent avec une fiche technique unique par produit.

### 1.4 Upload depuis EasyAdmin

L'upload est actuellement gere dans :

- `src/Controller/Admin/BookCrudController.php`
- `src/Controller/Admin/MovieCrudController.php`

Le champ de formulaire utilise `documentFile` dans `AbstractProduct`, puis :

1. EasyAdmin recoit l'`UploadedFile`
2. `DocumentStorage::store()` deplace le fichier dans `var/storage/documents`
3. un objet `Document` est cree ou mis a jour
4. ce `Document` est lie au produit

Le principe general est donc bon.

### 1.5 Telechargement actuel

Le telechargement actuel est gere dans `src/Controller/DocumentController.php` via :

`/api/document/{id}/download`

Cette route fonctionne a partir de l'identifiant du `Document`, pas a partir de l'identifiant du produit.

### 1.6 Authentification HTTP-only

Le projet utilise un JWT stocke dans un cookie HTTP-only :

- le login se fait dans `src/Controller/AuthController.php`
- le cookie s'appelle `BEARER`
- Lexik JWT lit le token depuis le cookie dans `config/packages/lexik_jwt_authentication.yaml`
- l'ensemble de `/api` est protege dans `config/packages/security.yaml`

Point important pour le front :

avec un cookie HTTP-only, le front React ne doit pas lire le token.
Le navigateur enverra automatiquement le cookie si la requete `fetch` utilise :

```ts
credentials: 'include'
```

et si le CORS l'autorise.

### 1.7 CORS actuel

`config/packages/nelmio_cors.yaml` autorise deja :

- `allow_credentials: true`
- `allow_origin: ['http://localhost:5173']`

Donc l'architecture est compatible avec un front React en dev sur Vite, a condition de faire les requetes avec `credentials: 'include'`.

## 2. Ce qui manque aujourd'hui pour le front

Le systeme d'upload et de stockage existe deja, mais l'API n'est pas encore optimisee pour ton besoin front.

Ton besoin reel n'est pas :

- recuperer l'objet `document`
- connaitre `document.id`
- construire une URL basee sur `document.id`

Ton besoin reel est :

- partir d'un produit
- cliquer sur un bouton
- telecharger la fiche technique liee a ce produit

La meilleure API pour ce besoin est donc une route de telechargement par produit, et non une route par document.

## 3. Solution recommandee

### 3.1 Idee generale

Ajouter deux routes backend :

- `/api/movie/{id}/download-document`
- `/api/book/{id}/download-document`

Ces routes feront :

1. retrouver le produit
2. recuperer le `Document` lie
3. retrouver le fichier physique via `storedName`
4. renvoyer ce fichier au navigateur

Avantages :

- le front ne manipule pas `document.id`
- le front n'a pas besoin que le JSON produit expose l'objet `document`
- le backend reste responsable de la resolution produit -> document -> fichier
- la solution est ideale avec une authentification HTTP-only

## 4. Code backend propose

### 4.1 Route de telechargement pour un film

Fichier cible : `src/Controller/MovieController.php`

```php
<?php

namespace App\Controller;

use App\Domain\MovieDomain;
use App\Service\DocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class MovieController extends AbstractController
{
    #[Route('/movie/{id}/download-document', name: 'app_movie_download_document', methods: ['GET'])]
    public function downloadDocument(
        int $id,
        MovieDomain $movieDomain,
        DocumentStorage $documentStorage
    ): BinaryFileResponse {
        $movie = $movieDomain->findMovieById($id);
        $document = $movie->getDocument();

        if ($document === null || $document->getStoredName() === null) {
            throw $this->createNotFoundException('Aucune fiche technique n est associee a ce film.');
        }

        $absolutePath = $documentStorage->getAbsolutePath($document->getStoredName());

        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Le fichier associe a ce film est introuvable.');
        }

        return $this->file(
            $absolutePath,
            $document->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
```

### 4.2 Route de telechargement pour un livre

Fichier cible : `src/Controller/BookController.php`

```php
<?php

namespace App\Controller;

use App\Domain\BookDomain;
use App\Service\DocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class BookController extends AbstractController
{
    #[Route('/book/{id}/download-document', name: 'app_book_download_document', methods: ['GET'])]
    public function downloadDocument(
        int $id,
        BookDomain $bookDomain,
        DocumentStorage $documentStorage
    ): BinaryFileResponse {
        $book = $bookDomain->findBookById($id);
        $document = $book->getDocument();

        if ($document === null || $document->getStoredName() === null) {
            throw $this->createNotFoundException('Aucune fiche technique n est associee a ce livre.');
        }

        $absolutePath = $documentStorage->getAbsolutePath($document->getStoredName());

        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Le fichier associe a ce livre est introuvable.');
        }

        return $this->file(
            $absolutePath,
            $document->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
```

### 4.3 Option de durcissement du controleur de document actuel

Le controleur `src/Controller/DocumentController.php` peut aussi etre renforce, car il ne verifie pas encore explicitement :

- que `storedName` n'est pas null
- que le fichier existe bien sur disque

Version plus robuste :

```php
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
```

## 5. Code React recommande avec cookie HTTP-only

### 5.1 Point important

Comme ton auth repose sur un cookie HTTP-only, le front React ne doit pas envoyer de header `Authorization` manuellement.

Il doit faire ses requetes avec :

```ts
credentials: 'include'
```

C'est le navigateur qui joindra automatiquement le cookie `BEARER`.

### 5.2 Helper de telechargement produit

```ts
type ProductType = 'movie' | 'book';

type DownloadProductDocumentParams = {
  apiBaseUrl: string;
  productType: ProductType;
  productId: number;
  fallbackFileName?: string;
};

export async function downloadProductDocument({
  apiBaseUrl,
  productType,
  productId,
  fallbackFileName = 'fiche-technique',
}: DownloadProductDocumentParams): Promise<void> {
  const response = await fetch(
    `${apiBaseUrl}/api/${productType}/${productId}/download-document`,
    {
      method: 'GET',
      credentials: 'include',
    }
  );

  if (!response.ok) {
    if (response.status === 401) {
      throw new Error('Utilisateur non authentifie.');
    }

    if (response.status === 404) {
      throw new Error('Aucune fiche technique disponible pour ce produit.');
    }

    throw new Error('Le telechargement de la fiche technique a echoue.');
  }

  const blob = await response.blob();
  const objectUrl = window.URL.createObjectURL(blob);

  const disposition = response.headers.get('Content-Disposition');
  const matchedFileName = disposition?.match(/filename="?([^\"]+)"?/i)?.[1];
  const fileName = matchedFileName ?? fallbackFileName;

  const link = document.createElement('a');
  link.href = objectUrl;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  link.remove();

  window.URL.revokeObjectURL(objectUrl);
}
```

### 5.3 Bouton React simple

```tsx
import { useState } from 'react';
import { downloadProductDocument } from './downloadProductDocument';

type ProductType = 'movie' | 'book';

type DownloadTechnicalSheetButtonProps = {
  productId: number;
  productType: ProductType;
  apiBaseUrl: string;
};

export function DownloadTechnicalSheetButton({
  productId,
  productType,
  apiBaseUrl,
}: DownloadTechnicalSheetButtonProps) {
  const [isDownloading, setIsDownloading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleClick() {
    try {
      setError(null);
      setIsDownloading(true);

      await downloadProductDocument({
        apiBaseUrl,
        productType,
        productId,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur inconnue');
    } finally {
      setIsDownloading(false);
    }
  }

  return (
    <div>
      <button type="button" onClick={handleClick} disabled={isDownloading}>
        {isDownloading ? 'Telechargement...' : 'Telecharger la fiche technique'}
      </button>

      {error ? <p>{error}</p> : null}
    </div>
  );
}
```

### 5.4 Exemple d'integration dans une carte produit

```tsx
import { DownloadTechnicalSheetButton } from './DownloadTechnicalSheetButton';

type ProductCardProps = {
  id: number;
  name: string;
  description: string;
  price: string;
  type: 'movie' | 'book';
  apiBaseUrl: string;
};

export function ProductCard({
  id,
  name,
  description,
  price,
  type,
  apiBaseUrl,
}: ProductCardProps) {
  return (
    <article>
      <h2>{name}</h2>
      <p>{description}</p>
      <p>{price} EUR</p>

      <DownloadTechnicalSheetButton
        productId={id}
        productType={type}
        apiBaseUrl={apiBaseUrl}
      />
    </article>
  );
}
```

## 6. Variante si tu veux afficher le bouton seulement quand un document existe

Si tu veux masquer le bouton quand il n'y a pas de fiche technique, tu as deux options.

### Option A

Ajouter une propriete booleenne serialisee dans le JSON produit, par exemple `hasDocument`.

Exemple dans `AbstractProduct` :

```php
#[Groups(['movie:read', 'book:read'])]
public function hasDocument(): bool
{
    return $this->document !== null;
}
```

Le front peut ensuite faire :

```tsx
{product.hasDocument ? (
  <DownloadTechnicalSheetButton
    productId={product.id}
    productType={product.type}
    apiBaseUrl={apiBaseUrl}
  />
) : null}
```

### Option B

Toujours afficher le bouton et laisser le backend renvoyer une `404` si aucun document n'est associe.

Pour un premier jet, l'option B est plus simple.

## 7. Points d'amelioration identifies dans le systeme actuel

### 7.1 `DocumentStorage::store()`

Il y a encore un detail a corriger pour etre plus propre :

```php
if ($file->getSize() > $maxsize) {
```

Tu as deja recupere la taille dans `$size`, donc il vaut mieux reutiliser `$size` plutot que rappeler `getSize()` :

```php
if ($size > $maxSize) {
```

### 7.2 `DocumentController`

Le telechargement par `document.id` marche, mais il est moins ergonomique pour le front que des routes basees sur `product.id`.

### 7.3 `Document::getDownloadUrl()`

Cette methode est utile si tu choisis une API centree sur le document, mais elle devient secondaire si tu choisis des routes `/movie/{id}/download-document` et `/book/{id}/download-document`.

### 7.4 Visibilite du document dans les reponses JSON

Aujourd'hui, `document` n'est pas expose dans les groupes de serialisation de `AbstractProduct`. Ce n'est pas un probleme si tu adoptes une route de telechargement par produit.

## 8. Recommandation finale

La solution la plus simple et la plus coherente avec ton systeme HTTP-only est :

1. garder le stockage actuel dans `var/storage/documents`
2. garder la relation `OneToOne` entre produit et document
3. ajouter une route `/api/movie/{id}/download-document`
4. ajouter une route `/api/book/{id}/download-document`
5. dans React, utiliser `fetch(..., { credentials: 'include' })`
6. declencher le telechargement avec un `Blob` et un lien temporaire

Cette solution a l'avantage d'etre simple pour le front, robuste cote backend, et parfaitement compatible avec un cookie JWT HTTP-only.
