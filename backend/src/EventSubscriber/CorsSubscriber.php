<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => ['onResponse', -100],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($event->getRequest()->getMethod() === 'OPTIONS') {
            $response = new Response('', Response::HTTP_NO_CONTENT);
            $this->addHeaders($response);
            $event->setResponse($response);
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        $this->addHeaders($event->getResponse());
    }

    private function addHeaders(Response $response): void
    {
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
