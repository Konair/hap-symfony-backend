<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class JsonRequestTransformerListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (empty($request->getContent())) {
            return;
        }

        if (!$this->isJsonRequest($request)) {
            return;
        }

        if (!$this->transformJsonBody($request)) {
            $response = new JsonResponse('Unable to parse request.', Response::HTTP_BAD_REQUEST);
            $event->setResponse($response);
        }
    }

    private function isJsonRequest(Request $request): bool
    {
        return 'json' === $request->getContentType();
    }

    private function transformJsonBody(Request $request): bool
    {
        $content = $request->getContent();

        if (!is_string($content)) {
            return false;
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if ($data === null) {
            return true;
        }

        $request->request->replace($data);
        return true;
    }
}
