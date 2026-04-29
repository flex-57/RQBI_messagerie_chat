<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Conversation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    public function testCreatePrivateConversation(): void
    {
        $conversation = new Conversation();
        $conversation->setType(Conversation::TYPE_PRIVATE);

        $this->assertEquals(Conversation::TYPE_PRIVATE, $conversation->getType());
        $this->assertNull($conversation->getName());
    }

    public function testCreateGroupConversation(): void
    {
        $conversation = new Conversation();
        $conversation->setType(Conversation::TYPE_GROUP);
        $conversation->setName('Test Group');

        $this->assertEquals(Conversation::TYPE_GROUP, $conversation->getType());
        $this->assertEquals('Test Group', $conversation->getName());
    }

    public function testAddMember(): void
    {
        $conversation = new Conversation();
        $user = $this->createMock(User::class);

        $conversation->addMember($user);

        $this->assertTrue($conversation->getMembers()->contains($user));
    }

    public function testRemoveMember(): void
    {
        $conversation = new Conversation();
        $user = $this->createMock(User::class);

        $conversation->addMember($user);
        $this->assertTrue($conversation->getMembers()->contains($user));

        $conversation->removeMember($user);
        $this->assertFalse($conversation->getMembers()->contains($user));
    }

    public function testInvalidConversationType(): void
    {
        $conversation = new Conversation();

        $this->expectException(\InvalidArgumentException::class);
        $conversation->setType('invalid_type');
    }
}
