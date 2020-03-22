<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Util\PasswordEncoder;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{RequestEvent, ExceptionEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtAuthorizationSubscriber implements EventSubscriberInterface {

    /** @var ManagerRegistry */
    private $entityManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var array */
    private $disabledRoutes = ['login', '', 'index', '_wdt'];

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $entityManager) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event) {
        $currentRoute = $event->getRequest()->attributes->get('_route');

        if (in_array($currentRoute, $this->disabledRoutes)) {
            return;
        }

        $authHeader = $event->getRequest()->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        $key = $_ENV['JWT_SECRET_KEY'];

        if ($this->tokenIsInvalid($token)) {
            throw new AccessDeniedHttpException('Invalid JWT token');
        }

        if (!$key) {
            throw new AccessDeniedHttpException('Bad jwt auth configuration');
        }

        /** @var \stdClass $payload */
        $payload = JWT::decode($token, $key, ['HS256']);
        if ($this->payloadIsInvalid($payload)) throw new \Exception();

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $payload->id,
            'username' => $payload->username,
        ]);

        if (!$user) throw new AccessDeniedHttpException('Unauthorized');

        $this->tokenStorage->getToken()->setUser($user);
    }

    private function tokenIsInvalid($token) {
        return !$token || count(explode('.', $token)) !== 3;
    }

    private function payloadIsInvalid(?\stdClass $payload) {
        return !$payload || !$payload->username || !$payload->id;
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}