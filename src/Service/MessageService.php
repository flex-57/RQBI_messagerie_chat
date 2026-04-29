<?php

namespace App\Service;

use App\Dto\MessageResponse;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    public function __construct(
        private MessageRepository $messageRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function sendMessage(Conversation $conversation, User $sender, string $content, User $currentUser): MessageResponse
    {
        if (!$conversation->getMembers()->contains($currentUser)) {
            throw new \Exception('Access denied');
        }

        $message = new Message();
        $message->setConversation($conversation);
        $message->setSender($sender);
        $message->setContent($content);

        $this->entityManager->persist($message);
        $conversation->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->convertToResponse($message);
    }

    public function getMessages(Conversation $conversation, User $currentUser, int $limit = 50, int $offset = 0): array
    {
        if (!$conversation->getMembers()->contains($currentUser)) {
            throw new \Exception('Access denied');
        }

        $messages = $this->messageRepository->findByConversation($conversation, $limit, $offset);
        $result = [];

        foreach ($messages as $message) {
            $result[] = $this->convertToResponse($message);
        }

        return array_reverse($result);
    }

    public function editMessage(Message $message, string $content, User $currentUser): MessageResponse
    {
        if ($message->getSender() !== $currentUser) {
            throw new \Exception('Access denied');
        }

        $message->setContent($content);
        $message->setEditedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->convertToResponse($message);
    }

    public function deleteMessage(Message $message, User $currentUser): void
    {
        if ($message->getSender() !== $currentUser) {
            throw new \Exception('Access denied');
        }

        $this->entityManager->remove($message);
        $this->entityManager->flush();
    }

    private function convertToResponse(Message $message): MessageResponse
    {
        $response = new MessageResponse();
        $response->id = (string) $message->getId();
        $response->content = $message->getContent();
        $response->senderName = $message->getSender()->getFullName();
        $response->senderId = (string) $message->getSender()->getId();
        $response->conversationId = (string) $message->getConversation()->getId();
        $response->createdAt = $message->getCreatedAt()->toDateTimeString();
        $response->editedAt = $message->getEditedAt()?->toDateTimeString();

        return $response;
    }
}
