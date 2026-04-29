<?php

namespace App\Service;

use App\Dto\ConversationResponse;
use App\Entity\Conversation;
use App\Entity\ConversationMember;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConversationService
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getConversationsForUser(User $user): array
    {
        $conversations = $this->conversationRepository->findByUser($user);
        $result = [];

        foreach ($conversations as $conversation) {
            $result[] = $this->convertToResponse($conversation);
        }

        return $result;
    }

    public function getConversation(string $id, User $user): ConversationResponse
    {
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation || !$conversation->getMembers()->contains($user)) {
            throw new \Exception('Conversation not found or access denied');
        }

        return $this->convertToResponse($conversation);
    }

    public function createPrivateConversation(User $currentUser, User $otherUser): ConversationResponse
    {
        $existing = $this->conversationRepository->findPrivateConversation($currentUser, $otherUser);
        if ($existing) {
            return $this->convertToResponse($existing);
        }

        $conversation = new Conversation();
        $conversation->setType(Conversation::TYPE_PRIVATE);
        $conversation->addMember($currentUser);
        $conversation->addMember($otherUser);

        $member1 = new ConversationMember();
        $member1->setConversation($conversation);
        $member1->setUser($currentUser);

        $member2 = new ConversationMember();
        $member2->setConversation($conversation);
        $member2->setUser($otherUser);

        $this->entityManager->persist($conversation);
        $this->entityManager->persist($member1);
        $this->entityManager->persist($member2);
        $this->entityManager->flush();

        return $this->convertToResponse($conversation);
    }

    public function createGroupConversation(User $creator, string $name, array $memberIds): ConversationResponse
    {
        $conversation = new Conversation();
        $conversation->setType(Conversation::TYPE_GROUP);
        $conversation->setName($name);
        $conversation->addMember($creator);

        $creatorMember = new ConversationMember();
        $creatorMember->setConversation($conversation);
        $creatorMember->setUser($creator);
        $this->entityManager->persist($creatorMember);

        foreach ($memberIds as $memberId) {
            if ($memberId === (string) $creator->getId()) {
                continue;
            }
            // Find and add member (would need user lookup)
        }

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $this->convertToResponse($conversation);
    }

    private function convertToResponse(Conversation $conversation): ConversationResponse
    {
        $response = new ConversationResponse();
        $response->id = (string) $conversation->getId();
        $response->name = $conversation->getName();
        $response->type = $conversation->getType();
        $response->createdAt = $conversation->getCreatedAt()->toDateTimeString();
        $response->updatedAt = $conversation->getUpdatedAt()->toDateTimeString();

        foreach ($conversation->getMembers() as $member) {
            $response->members[] = [
                'id' => (string) $member->getId(),
                'name' => $member->getFullName(),
                'email' => $member->getEmail(),
            ];
        }

        $messages = $conversation->getMessages();
        if (count($messages) > 0) {
            $lastMessage = $messages->last();
            if ($lastMessage) {
                $response->lastMessage = $lastMessage->getContent();
                $response->lastMessageTime = $lastMessage->getCreatedAt()->toDateTimeString();
            }
        }

        return $response;
    }
}
