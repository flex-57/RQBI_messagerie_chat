<?php

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageApiTest extends WebTestCase
{
    private $client;
    private $userToken;
    private $conversationId;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Create user
        $this->client->request('POST', '/api/auth/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Message',
                'lastName' => 'Test',
                'email' => 'msg.test@test.com',
                'password' => 'Password123'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->userToken = $response['token'];

        // Create conversation
        $this->client->request('POST', '/api/conversations/group', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['name' => 'Test Conversation'])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->conversationId = $response['id'];
    }

    public function testSendMessage(): void
    {
        $this->client->request('POST', "/api/conversations/{$this->conversationId}/messages", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => 'Test message'])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Test message', $response['content']);
        $this->assertNotEmpty($response['id']);
        $this->assertNotEmpty($response['createdAt']);
    }

    public function testGetMessages(): void
    {
        // Send a message first
        $this->client->request('POST', "/api/conversations/{$this->conversationId}/messages", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => 'Test message'])
        );

        // Get messages
        $this->client->request('GET', "/api/conversations/{$this->conversationId}/messages", [], [],
            ['HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response['messages']);
        $this->assertEquals(1, count($response['messages']));
        $this->assertEquals('Test message', $response['messages'][0]['content']);
    }

    public function testEditMessage(): void
    {
        // Send a message
        $this->client->request('POST', "/api/conversations/{$this->conversationId}/messages", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => 'Original message'])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $messageId = $response['id'];

        // Edit the message
        $this->client->request('PATCH', "/api/conversations/{$this->conversationId}/messages/{$messageId}", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => 'Updated message'])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated message', $response['content']);
        $this->assertNotEmpty($response['editedAt']);
    }

    public function testDeleteMessage(): void
    {
        // Send a message
        $this->client->request('POST', "/api/conversations/{$this->conversationId}/messages", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => 'Message to delete'])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $messageId = $response['id'];

        // Delete the message
        $this->client->request('DELETE', "/api/conversations/{$this->conversationId}/messages/{$messageId}", [], [],
            ['HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"]
        );

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    public function testSendEmptyMessage(): void
    {
        $this->client->request('POST', "/api/conversations/{$this->conversationId}/messages", [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer {$this->userToken}"
            ],
            json_encode(['content' => ''])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
