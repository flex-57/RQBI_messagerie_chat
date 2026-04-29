<?php

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterEndpoint(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'email' => 'jean.dupont@test.com',
                'password' => 'SecurePassword123'
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('jean.dupont@test.com', $response['email']);
        $this->assertEquals('Jean', $response['firstName']);
        $this->assertNotEmpty($response['token']);
    }

    public function testRegisterWithMissingFields(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com'
                // Missing firstName, lastName, password
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testLoginEndpoint(): void
    {
        // First register a user
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'email' => 'login.test@test.com',
                'password' => 'TestPassword123'
            ])
        );

        // Then login
        $this->client->request('POST', '/api/auth/login', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'login.test@test.com',
                'password' => 'TestPassword123'
            ])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('login.test@test.com', $response['email']);
        $this->assertNotEmpty($response['token']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'nonexistent@test.com',
                'password' => 'WrongPassword'
            ])
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}
