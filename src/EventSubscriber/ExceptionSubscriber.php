<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if($exception instanceof HttpException) {
            $status = $exception->getStatusCode();

            switch($status) {
                case 404:
                    $message = 'Ressource non trouvée.';
                    break;
                case 400:
                    $message = 'La requête est invalide. Veuillez vérifier tous les champs.';
                    break;
            }
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $message ?? $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
        }else{
            $data = [
                'status' => 500,
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
