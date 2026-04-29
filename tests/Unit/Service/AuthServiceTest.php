<?php

namespace App\Tests\Unit\Service;

use App\Dto\LoginRequest;
use App\Dto\RegisterUserRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository $userRepository;
    private PasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->passwordHasher,
            $this->entityManager
        );
    }

    public function testRegisterNewUser(): void
    {
        $request = new RegisterUserRequest();
        $request->firstName = 'Jean';
        $request->lastName = 'Dupont';
        $request->email = 'jean@test.com';
        $request->password = 'password123';

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('jean@test.com')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->willReturn('hashed_password');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->authService->register($request);

        $this->assertEquals('jean@test.com', $response->email);
        $this->assertEquals('Jean', $response->firstName);
        $this->assertEquals('Dupont', $response->lastName);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $request = new RegisterUserRequest();
        $request->email = 'existing@test.com';

        $existingUser = $this->createMock(User::class);
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingUser);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already exists');

        $this->authService->register($request);
    }

    public function testLoginWithValidCredentials(): void
    {
        $request = new LoginRequest();
        $request->email = 'jean@test.com';
        $request->password = 'password123';

        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('jean@test.com');
        $user->method('getPassword')->willReturn('hashed_password');
        $user->method('getFullName')->willReturn('Jean Dupont');
        $user->method('getId')->willReturn('123');
        $user->method('getCreatedAt')->willReturn(new \DateTimeImmutable());

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('verify')
            ->with('password123', 'hashed_password')
            ->willReturn(true);

        $response = $this->authService->login($request);

        $this->assertEquals('jean@test.com', $response->email);
        $this->assertNotEmpty($response->token);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $request = new LoginRequest();
        $request->email = 'jean@test.com';
        $request->password = 'wrong_password';

        $user = $this->createMock(User::class);
        $user->method('getPassword')->willReturn('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login($request);
    }
}
