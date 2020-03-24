<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface {

    public function onException(ExceptionEvent $event) {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            $response = new Response();
            $response->setStatusCode($exception->getStatusCode());
            $response->setContent(json_encode(['message' => $exception->getMessage()]));
            $response->headers->set('Content-Type', 'application/json');
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }
}