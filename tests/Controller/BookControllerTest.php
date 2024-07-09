<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookControllerTest  extends WebTestCase
{

    public function testCreate(): void
    {
        $client = static::createClient();

        $imagePath = __DIR__ . '/../test_files/test_book_image.jpg';
        $imageFile = new UploadedFile(
            $imagePath,
            'test_book_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $client->request('POST', '/api/books/create', [], ['imageFile' => $imageFile], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'title' => 'Book Title',
            'description' => 'Book Description',
            'publication_date' => '2024-07-09',
            'authors' => [1, 2]
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        $client = static::createClient();

        $bookId = 1; // Test book ID

        $imagePath = __DIR__ . '/../test_files/test_book_image.jpg';
        $imageFile = new UploadedFile(
            $imagePath,
            'test_book_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $client->request('PUT', '/api/books/' . $bookId, [], ['imageFile' => $imageFile], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'title' => 'Book Title Changed',
            'description' => 'Book Description Changed',
            'authors' => [1, 2, 3]
        ]));

        $updatedBookData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Book Title Changed', $updatedBookData['title']);
        $this->assertEquals('Book Description Changed', $updatedBookData['description']);
    }
}
