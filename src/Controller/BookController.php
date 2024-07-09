<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;
use App\Entity\Book;

#[Route('/api/books', name: 'api_book')]
class BookController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $books = $this->entityManager->getRepository(Book::class)->findPaginatedBooks($page, $limit);
        $jsonBooks = $serializer->serialize($books, 'json',  ['groups' => 'book:read']);
        return new JsonResponse($jsonBooks, Response::HTTP_OK, [], true);
    }

    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $filters = json_decode($request->getContent(), true);

        $books = $this->entityManager->getRepository(Book::class)->findPaginatedBooks($page, $limit, $filters);
        $jsonBooks = $serializer->serialize($books, 'json',  ['groups' => 'book:read']);
        return new JsonResponse($jsonBooks, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $book = new Book();
        return $this->saveBook($book, $request, $validator, $serializer, true);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json(['message' => 'Book not found'], 404);
        }

        return $this->saveBook($book, $request, $validator, $serializer, false);
    }

    private function saveBook(Book $book, Request $request, ValidatorInterface $validator, SerializerInterface $serializer, bool $isNew ): JsonResponse
    {
        // Set Data
        $data = json_decode($request->getContent(), true);
        $book->setTitle($data['title'] ?? '');
        $book->setDescription($data['description'] ?? '');
        $book->setPublicationDate(isset($data['publication_date']) ? new \DateTime($data['publication_date']) : null);

        // Image Download
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            try {
                $book->setImageFile($imageFile);
                $destination = $this->getParameter('images_directory') . '/' . $book->getImage();

                // Copy the file
                if (!copy($imageFile->getPathname(), $destination)) {
                    throw new FileException('Failed to copy the file');
                }

                // Delete the temporary file if not in test environment
                if ($this->getParameter('kernel.environment') !== 'test') {
                    unlink($imageFile->getPathname());
                }

            } catch (FileException $e) {
                return new JsonResponse(['error' => 'File could not be uploaded.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Set authors
        $book->getAuthors()->clear();
        foreach ($data['authors'] as $authorId) {
            $author = $this->entityManager->getRepository(Author::class)->find($authorId);
            if ($author) {
                $book->addAuthor($author);
            }
        }

        // Validate
        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($errorMessages, JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($isNew) {
            $this->entityManager->persist($book);
        }
        $this->entityManager->flush();

        // Return Data
        $jsonBook = $serializer->serialize($book, 'json',  ['groups' => 'book:read']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonBook = $serializer->serialize($book, 'json',  ['groups' => 'book:read']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }
}
