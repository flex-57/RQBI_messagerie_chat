<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/conversations')]
class ConversationController extends AbstractController
{
    public function __construct(
        private ConversationService $conversationService,
        private ConversationRepository $conversationRepository,
        private UserRepository $userRepository,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] $user): JsonResponse
    {
        try {
            $conversations = $this->conversationService->getConversationsForUser($user);
            return $this->json($conversations);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id, #[CurrentUser] $user): JsonResponse
    {
        try {
            $conversation = $this->conversationService->getConversation($id, $user);
            return $this->json($conversation);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/private/{userId}', methods: ['POST'])]
    public function createPrivate(string $userId, #[CurrentUser] $user): JsonResponse
    {
        try {
            $otherUser = $this->userRepository->find($userId);
            if (!$otherUser) {
                return $this->json(['error' => 'User not found'], 404);
            }

            $conversation = $this->conversationService->createPrivateConversation($user, $otherUser);
            return $this->json($conversation, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/group', methods: ['POST'])]
    public function createGroup(Request $request, #[CurrentUser] $user): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $name = $data['name'] ?? null;
            $memberIds = $data['members'] ?? [];

            if (!$name) {
                return $this->json(['error' => 'Group name is required'], 400);
            }

            $conversation = $this->conversationService->createGroupConversation($user, $name, $memberIds);
            return $this->json($conversation, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
