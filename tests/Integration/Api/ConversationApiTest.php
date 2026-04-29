<?php

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConversationApiTest extends WebTestCase
{
    private $client;
    private $userToken;
    private $userId;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Create a test user
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Test',
                'lastName' => 'User',
                'email' => 'conv.test@test.com',
                'password' => 'Password123'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->userToken = $response['token'];
        $this->userId = $response['id'];
    }

    public function testGetConversations(): void
    {
        $this->client->request('GET', '/api/conversations', [], [],
            ['HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testCreateGroupConversation(): void
    {
        $this->client->request('POST', '/api/conversations/group', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode([
                'name' => 'Test Group',
                'members' => []
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Test Group', $response['name']);
        $this->assertEquals('group', $response['type']);
    }

    public function testCreatePrivateConversation(): void
    {
        // Create second user
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'User',
                'lastName' => 'Two',
                'email' => 'user.two@test.com',
                'password' => 'Password123'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $userId2 = $response['id'];

        // Create private conversation
        $this->client->request('POST', "/api/conversations/private/{$userId2}", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ]
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('private', $response['type']);
    }

    public function testGetConversationWithoutAuth(): void
    {
        $this->client->request('GET', '/api/conversations');

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}
