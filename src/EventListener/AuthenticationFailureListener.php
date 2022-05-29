<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationFailureListener
{
    public const TESTER_EMAIL = 'test@test.com';
    public const TESTER_PASSWORD = 'test';

    public function __construct(
        private JWTTokenManagerInterface $tokenManager,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher
    )
    {
    }

    /**
     * @throws \JsonException
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $content = $event->getRequest()?->getContent();

        if ($content) {

            $testUser = $this->fakeUser((string)$content);

            if ($testUser) {
                $response = new JWTAuthenticationSuccessResponse($this->tokenManager->create($testUser));
                $event->setResponse($response);
                return;
            }
        }

        $response = new JWTAuthenticationFailureResponse('Invalid credentials', 401);
        $event->setResponse($response);
    }

    /**
     * @throws \JsonException
     */
    private function fakeUser(string $content): User|null
    {
        $decoded = json_decode($content, false, 512, JSON_THROW_ON_ERROR);

        if (isset($decoded->email, $decoded->password) && $decoded->email === self::TESTER_EMAIL && $decoded->password === self::TESTER_PASSWORD) {
            $testUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::TESTER_EMAIL]);

            if (!$testUser) {
                $testUser = (new User())
                    ->setEmail(self::TESTER_EMAIL)
                    ->setRoles(['ROLE_USER']);

                $testUser->setPassword($this->hasher->hashPassword($testUser, self::TESTER_PASSWORD));

                $this->entityManager->persist($testUser);
                $this->entityManager->flush();
            }

            return $testUser;
        }

        return null;
    }
}