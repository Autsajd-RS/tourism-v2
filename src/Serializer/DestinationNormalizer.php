<?php

namespace App\Serializer;

use App\Entity\Destination;
use App\Entity\DestinationLike;
use App\Entity\User;
use App\Repository\DestinationLikeRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class DestinationNormalizer implements NormalizerInterface
{
    public function __construct(
        private DestinationLikeRepository $likeRepository,
        private Security $security,
        private ObjectNormalizer $normalizer
    )
    {
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if (!$object instanceof Destination) {
            return $this->normalizer->normalize(object: $object);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->normalizer->normalize(object: $object, context: ['groups' => Destination::GROUP_READ]);
        }

        /** @var DestinationLike[] $liked */
        $liked = $this->likeRepository->isLikedByUser(destination: $object, user: $user);
        /** @var DestinationLike[] $disliked */
        $disliked = $this->likeRepository->isDislikedByUser(destination: $object, user: $user);

        if (count($liked) && !$liked[0]->isDeleted()) {
            $object->setLikedByMe(true);
        }

        if (count($disliked) && !$disliked[0]->isDeleted()) {
            $object->setDislikedByMe(true);
        }

        if ($object->getCity()?->getId() === $user->getCity()?->getId()) {
            $object->setNearMe(true);
        }

        $object->setLikesCount($this->likeRepository->likeCount(destination: $object));
        $object->setDislikesCount($this->likeRepository->dislikeCount(destination: $object));

        return $this->normalizer->normalize(object: $object, context: ['groups' => Destination::GROUP_READ]);
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof Destination;
    }
}