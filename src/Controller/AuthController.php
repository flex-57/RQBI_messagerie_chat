<?php

namespace App\Controller;

use App\Dto\LoginRequest;
use App\Dto\RegisterUserRequest;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(private AuthService $authService)
    {
    }

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $registerRequest = new RegisterUserRequest();
            $registerRequest->firstName = $data['firstName'] ?? '';
            $registerRequest->lastName = $data['lastName'] ?? '';
            $registerRequest->email = $data['email'] ?? '';
            $registerRequest->password = $data['password'] ?? '';

            if (!$registerRequest->firstName || !$registerRequest->lastName || !$registerRequest->email || !$registerRequest->password) {
                return $this->json(['error' => 'Missing required fields'], 400);
            }

            $user = $this->authService->register($registerRequest);

            return $this->json($user, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $loginRequest = new LoginRequest();
            $loginRequest->email = $data['email'] ?? '';
            $loginRequest->password = $data['password'] ?? '';

            if (!$loginRequest->email || !$loginRequest->password) {
                return $this->json(['error' => 'Missing email or password'], 400);
            }

            $user = $this->authService->login($loginRequest);

            return $this->json($user);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }
}
