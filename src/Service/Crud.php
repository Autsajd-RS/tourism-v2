<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\DTO\ForgotPasswordCredentials;
use App\DTO\PasswordChangeCredentials;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Destination;
use App\Entity\WishList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\string;

class Crud
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer
    )
    {
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param Request $request
     * @param string $entityClass
     * @return mixed
     */
    public function createFromRequest(Request $request, string $entityClass): mixed
    {
        return $this->serializer->deserialize((string)$request->getContent(), $entityClass, 'json');
    }

    public function validateEntity(mixed $entity, string $group = null): ConstraintViolationListInterface
    {
        if ($group) {
            return $this->validator->validate($entity, null, ['groups' => $group]);
        }
        return $this->validator->validate($entity);
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

    public function create(mixed $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function deserializeEntity(Request $request, string $entityClass): mixed
    {
        $entity = $this->createFromRequest(request: $request, entityClass: $entityClass);

        if (!$entity) {
            return new ErrorResponse(
                message:  'Denormalization failed',
                errors: ['request' => 'Bad request']
            );
        }

        $violations = $this->validateEntity($entity);

        if (count($violations) > 0) {
            return new ErrorResponse(
                message: 'Entity is not valid',
                errors: self::formatViolations($violations)
            );
        }

        return $entity;
    }

    public function extractCityFromRequest(Request $request): ErrorResponse|City
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'Server Error', errors: ['server' => $e->getMessage()]);
        }

        if (!isset($params->city)) {
            return new ErrorResponse(message: 'Request Error', errors: ['request' => 'bad request']);
        }

        $city = $this->entityManager->getRepository(City::class)->find($params->city);

        if (!$city) {
            return new ErrorResponse(message: 'Request Error', errors: ['city' => 'not found']);
        }

        return $city;
    }

    public function extractCategoryFromRequest(Request $request): ErrorResponse|Category
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'Server Error', errors: ['server' => $e->getMessage()]);
        }

        if (!isset($params->category)) {
            return new ErrorResponse(message: 'Request Error', errors: ['request' => 'bad request']);
        }

        $category = $this->entityManager->getRepository(Category::class)->find($params->category);

        if (!$category) {
            return new ErrorResponse(message: 'Request Error', errors: ['category' => 'not found']);
        }

        return $category;
    }

    public function extractDestinationFromRequest(Request $request): ErrorResponse|Destination
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'Server Error', errors: ['server' => $e->getMessage()]);
        }

        if (!isset($params->destination)) {
            return new ErrorResponse(message: 'Request Error', errors: ['request' => 'bad request']);
        }

        $destination = $this->entityManager->getRepository(Destination::class)->find($params->destination);

        if (!$destination) {
            return new ErrorResponse(message: 'Request Error', errors: ['category' => 'not found']);
        }

        return $destination;
    }

    public function extractListFromRequest(Request $request): ErrorResponse|WishList
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'Server Error', errors: ['server' => $e->getMessage()]);
        }

        if (!isset($params->list)) {
            return new ErrorResponse(message: 'Request Error', errors: ['request' => 'bad request']);
        }

        $list = $this->entityManager->getRepository(WishList::class)->find($params->list);

        if (!$list) {
            return new ErrorResponse(message: 'Request Error', errors: ['list' => 'not found']);
        }

        return $list;
    }

    public function extractChangePasswordCredentialsFromRequest(Request $request): ErrorResponse|PasswordChangeCredentials
    {
        $credentials = $this->serializer->deserialize((string)$request->getContent(), PasswordChangeCredentials::class, 'json');

        if (!$credentials instanceof PasswordChangeCredentials) {
            return new ErrorResponse(
                message: 'Bad request',
                errors: ['request' => 'Bad request']
            );
        }

        $violations = $this->validateEntity(entity: $credentials);

        if (count($violations)) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: self::formatViolations($violations)
            );
        }

        return $credentials;
    }

    public function extractForgotPasswordCredentialsFromRequest(Request $request): ErrorResponse|ForgotPasswordCredentials
    {
        $credentials = $this->serializer->deserialize((string)$request->getContent(), ForgotPasswordCredentials::class, 'json');

        if (!$credentials instanceof ForgotPasswordCredentials) {
            return new ErrorResponse(
                message: 'Bad request',
                errors: ['request' => 'Bad request']
            );
        }

        $violations = $this->validateEntity(entity: $credentials);

        if (count($violations)) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: self::formatViolations($violations)
            );
        }

        return $credentials;
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalizeEntity(mixed $entity, string $group = 'default'): array
    {
        return $this->normalizer->normalize($entity, null, ['groups' => $group]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function arrayToEntity(array $context, string $entityClass)
    {
        return $this->denormalizer->denormalize($context, $entityClass);
    }

    /**
     * @throws \JsonException
     */
    public function normalizeRequestContent(Request $request): array
    {
        return json_decode((string)$request->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws ExceptionInterface
     */
    public function partialUpdate(mixed $entity, string $entityPatchGroup, array $updateContext, array $excludedProperties = []): mixed
    {
        $entityArray = $this->normalizeEntity(entity: $entity, group: $entityPatchGroup);

        foreach ($entityArray as $property => $value) {
            if (isset($updateContext[$property]) && !in_array($property, $excludedProperties, true)) {
                $entity->{'set' . ucfirst($property)}($updateContext[$property]);
            }
        }

        return $entity;
    }

    public function patch(mixed $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function remove(mixed $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function refresh(mixed $entity): void
    {
        $this->entityManager->refresh($entity);
    }
}