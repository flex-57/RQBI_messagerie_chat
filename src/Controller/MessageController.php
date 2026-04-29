<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use App\Service\MercurePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/conversations/{conversationId}/messages')]
class MessageController extends AbstractController
{
    public function __construct(
        private MessageService $messageService,
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
        private MercurePublisher $mercurePublisher,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(string $conversationId, Request $request, #[CurrentUser] $user): JsonResponse
    {
        try {
            $conversation = $this->conversationRepository->find($conversationId);
            if (!$conversation) {
                return $this->json(['error' => 'Conversation not found'], 404);
            }

            $limit = (int) $request->query->get('limit', 50);
            $offset = (int) $request->query->get('offset', 0);

            $messages = $this->messageService->getMessages($conversation, $user, $limit, $offset);
            return $this->json(['messages' => $messages, 'total' => $this->messageRepository->countByConversation($conversation)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('', methods: ['POST'])]
    public function send(string $conversationId, Request $request, #[CurrentUser] $user): JsonResponse
    {
        try {
            $conversation = $this->conversationRepository->find($conversationId);
            if (!$conversation) {
                return $this->json(['error' => 'Conversation not found'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $content = $data['content'] ?? null;

            if (!$content) {
                return $this->json(['error' => 'Message content is required'], 400);
            }

            $message = $this->messageService->sendMessage($conversation, $user, $content, $user);

            $this->mercurePublisher->publishNewMessage((string) $conversation->getId(), [
                'id' => $message->id,
                'content' => $message->content,
                'senderName' => $message->senderName,
                'senderId' => $message->senderId,
                'createdAt' => $message->createdAt,
            ]);

            return $this->json($message, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{messageId}', methods: ['PATCH'])]
    public function edit(string $conversationId, string $messageId, Request $request, #[CurrentUser] $user): JsonResponse
    {
        try {
            $message = $this->messageRepository->find($messageId);
            if (!$message) {
                return $this->json(['error' => 'Message not found'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $content = $data['content'] ?? null;

            if (!$content) {
                return $this->json(['error' => 'Message content is required'], 400);
            }

            $message = $this->messageService->editMessage($message, $content, $user);
            return $this->json($message);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{messageId}', methods: ['DELETE'])]
    public function delete(string $conversationId, string $messageId, #[CurrentUser] $user): JsonResponse
    {
        try {
            $message = $this->messageRepository->find($messageId);
            if (!$message) {
                return $this->json(['error' => 'Message not found'], 404);
            }

            $this->messageService->deleteMessage($message, $user);
            return $this->json(null, 204);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
