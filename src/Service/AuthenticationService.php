<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\City;
use App\Entity\User;
use App\Message\UserRegistered;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationService
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hasher,
        private MessageBusInterface $messageBus
    )
    {
    }

    public function userFromRequest(Request $request, ?array $groups = null): ?User
    {
        $context = [];

        if ($groups) {
            $context = ['groups' => $groups];
        }

        return $this->serializer->deserialize((string)$request->getContent(), User::class, 'json', $context);
    }

    public function bindCity(Request $request, User $user): ErrorResponse|User
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new ErrorResponse(
                message: 'Registration Error',
                errors: [
                    'request' => 'processing error'
                ]
            );
        }

        if (!isset($params->city)) {
            return new ErrorResponse(
                message: 'Registration Error',
                errors: [
                    'request' => 'bad request'
                ]
            );
        }

        $city = $this->entityManager->getRepository(City::class)->find($params->city);

        if (!$city) {
            return new ErrorResponse(
                message: 'Registration Error',
                errors: [
                    'city' => 'not found'
                ]
            );
        }

        return $user->setCity($city);
    }

    public function validateUser(User $user, ?array $groups = null): ConstraintViolationListInterface
    {
        return $this->validator->validate($user);
    }

    public static function formatViolations(ConstraintViolationListInterface $violationList): array
    {
        $messages = [];
        foreach ($violationList as $violation) {
            /** @var ConstraintViolation $violation */
            $messages[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $messages;
    }

    public static function verificationCode(): string
    {
        return Uuid::uuid4();
    }

    public function registerUser(User $user): User
    {
        $user
            ->setVerificationToken(self::verificationCode())
            ->setVerificationTokenExpire((new \DateTime('now'))->modify('+5 minutes'))
            ->setRoles([User::ROLE_USER])
            ->setAvatar(User::DEFAULT_AVATAR);

        $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));

        $user->eraseCredentials();

        $this->messageBus->dispatch(new UserRegistered(
            email: $user->getEmail(),
            verificationCode: $user->getVerificationToken()
        ));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function verifyUser(string $verificationCode): User|ErrorResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $verificationCode]);

        if (!$user) {
            return new ErrorResponse(
                message: 'Verification failed',
                errors: [
                    'user' => 'not found'
                ]
            );
        }

        $now = new \DateTime();

        if ($now > $user->getVerificationTokenExpire()) {
            return new ErrorResponse(
                message: 'Verification failed',
                errors: [
                    'user' => 'verification token expired'
                ]
            );
        }

        $user
            ->setVerificationToken(null)
            ->setVerificationTokenExpire(null)
            ->setVerified(true);

        $this->entityManager->flush();

        return $user;
    }
}