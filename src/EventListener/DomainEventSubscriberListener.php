<?php

declare(strict_types=1);

namespace App\EventListener;

use Konair\HAP\Shared\Domain\Model\EventStore\EventStore;
use Konair\HAP\Shared\Domain\Service\EventSubscriber\DomainEventPublisher;
use Konair\HAP\Shared\Infrastructure\Domain\Service\EventSubscriber\Persist\PersistSubscriber;

final class DomainEventSubscriberListener
{
    public function __construct(
        private EventStore $eventStore,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function onKernelRequest(): void
    {
        $this->domainEventPublisher->subscribe(new PersistSubscriber($this->eventStore));
    }
}
