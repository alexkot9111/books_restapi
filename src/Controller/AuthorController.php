<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/authors', name: 'api_author')]
class AuthorController extends AbstractController
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

        $authors = $this->entityManager->getRepository(Author::class)->findPaginatedAuthors($page, $limit);
        $jsonAuthors = $serializer->serialize($authors, 'json',  ['groups' => 'author:read']);
        return new JsonResponse($jsonAuthors, Response::HTTP_OK, [], true);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request,  ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $author = new Author();
        $author->setLastName($data['last_name'] ?? '');
        $author->setFirstName($data['first_name'] ?? '');
        $author->setSurName($data['sur_name'] ?? '');

        $errors = $validator->validate($author);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($errorMessages, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        $jsonAuthor = $serializer->serialize($author, 'json',  ['groups' => 'author:read']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }
}
