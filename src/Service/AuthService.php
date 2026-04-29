<?php

namespace App\Service;

use App\Dto\LoginRequest;
use App\Dto\RegisterUserRequest;
use App\Dto\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function register(RegisterUserRequest $request): UserResponse
    {
        $existingUser = $this->userRepository->findByEmail($request->email);
        if ($existingUser) {
            throw new \Exception('User with this email already exists');
        }

        $user = new User();
        $user->setEmail($request->email);
        $user->setFirstName($request->firstName);
        $user->setLastName($request->lastName);
        $user->setPassword($this->passwordHasher->hash($request->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->createUserResponse($user);
    }

    public function login(LoginRequest $request): UserResponse
    {
        $user = $this->userRepository->findByEmail($request->email);
        if (!$user) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (!$this->passwordHasher->verify($request->password, $user->getPassword())) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $this->createUserResponse($user);
    }

    private function createUserResponse(User $user): UserResponse
    {
        $response = new UserResponse();
        $response->id = (string) $user->getId();
        $response->email = $user->getEmail();
        $response->firstName = $user->getFirstName();
        $response->lastName = $user->getLastName();
        $response->fullName = $user->getFullName();
        $response->createdAt = $user->getCreatedAt()->toDateTimeString();
        $response->token = base64_encode($user->getEmail());

        return $response;
    }
}
