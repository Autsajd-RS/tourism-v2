<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InvalidTokenListener
{
    public const TEST_TOKEN = 'Bearer test-token';

    public function __construct(
        private JWTTokenManagerInterface $tokenManager,
        private HttpClientInterface $meClient,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function onTokenInvalid(JWTInvalidEvent $event): void
    {
        $request = $event->getRequest();
        $authorization = $request?->headers->get('authorization');

        if ($authorization && $authorization === self::TEST_TOKEN && $request) {
            try {
                $responseFromRepeated = $this->repeatRequest($request);
                $responseToReturn = new Response();
                $responseToReturn->setContent($responseFromRepeated?->getContent());
                $responseToReturn->headers->add($responseFromRepeated?->getHeaders());
                $event->setResponse($responseToReturn);
                return;
            } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
                $response = new JWTAuthenticationFailureResponse('I cannot fake this request, sorry :)', Response::HTTP_I_AM_A_TEAPOT);
                $event->setResponse($response);
                return;
            }
        }

        $response = new JWTAuthenticationFailureResponse('Invalid token', 403);
        $event->setResponse($response);
    }

    private function repeatRequest(Request $request): ?ResponseInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => AuthenticationFailureListener::TESTER_EMAIL]);

        if ($user) {
            $request->headers->set('authorization', 'Bearer ' . $this->tokenManager->create($user));

            if ($request->getMethod() === Request::METHOD_GET) {
                try {
                    return $this->meClient->request($request->getMethod(), $request->getRequestUri(), [
                        'headers' => $request->headers->all()
                    ]);
                } catch (TransportExceptionInterface) {
                }
            } else {
                try {
                    return $this->meClient->request($request->getMethod(), $request->getRequestUri(), [
                        'headers' => $request->headers->all(),
                        'json' => $request->getContent()
                    ]);
                } catch (TransportExceptionInterface) {
                }
            }
        }

        return null;
    }
}