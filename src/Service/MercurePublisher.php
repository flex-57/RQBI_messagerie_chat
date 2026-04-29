<?php

namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher
{
    public function __construct(private HubInterface $hub)
    {
    }

    public function publishNewMessage(string $conversationId, array $messageData): void
    {
        $update = new Update(
            "conversation/{$conversationId}/messages",
            json_encode($messageData)
        );

        $this->hub->publish($update);
    }

    public function publishConversationUpdate(string $conversationId, array $conversationData): void
    {
        $update = new Update(
            "conversation/{$conversationId}",
            json_encode($conversationData)
        );

        $this->hub->publish($update);
    }

    public function publishUserConversationList(string $userId, array $conversations): void
    {
        $update = new Update(
            "user/{$userId}/conversations",
            json_encode($conversations)
        );

        $this->hub->publish($update);
    }

    public function publishUserIsTyping(string $conversationId, string $userId, string $userName): void
    {
        $update = new Update(
            "conversation/{$conversationId}/typing",
            json_encode(['userId' => $userId, 'userName' => $userName])
        );

        $this->hub->publish($update);
    }
}
