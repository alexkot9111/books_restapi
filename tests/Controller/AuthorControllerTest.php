<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthorControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $client->request('POST', '/api/authors/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'first_name' => 'Fname',
            'last_name' => 'Lname',
            'sur_name' => 'Sname',
        ]));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('first_name', $responseData);
        $this->assertArrayHasKey('last_name', $responseData);
        $this->assertArrayHasKey('sur_name', $responseData);

        $this->assertEquals('Fname', $responseData['first_name']);
        $this->assertEquals('Lname', $responseData['last_name']);
        $this->assertEquals('Sname', $responseData['sur_name']);
    }
}
