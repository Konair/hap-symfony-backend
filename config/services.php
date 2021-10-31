<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\EventListener\DisableAliasAnnotationListener;
use App\EventListener\DomainEventSubscriberListener;
use App\EventListener\JsonRequestTransformerListener;
use App\SerializationHandler\AccessPlanSerializationHandler;
use App\SerializationHandler\CollectionSerializationHandler;
use App\SerializationHandler\VatSerializationHandler;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Konair\HAP\Payment\Domain\Model\Billing\BillingDataRepository;
use Konair\HAP\Payment\Domain\Model\Cart\CartRepository;
use Konair\HAP\Payment\Domain\Model\Price\PricePlanRepository;
use Konair\HAP\Payment\Infrastructure\Domain\Model\Billing\EventStoreBillingDataRepository;
use Konair\HAP\Payment\Infrastructure\Domain\Model\Cart\EventStoreCartRepository;
use Konair\HAP\Payment\Infrastructure\Domain\Model\PricePlan\EventStorePricePlanRepository;
use Konair\HAP\Payment\Infrastructure\Domain\Service\PaymentProvider\PaymentProviderFactory;
use Konair\HAP\Shared\Domain\Model\EventStore\EventStore;
use Konair\HAP\Shared\Domain\Service\EventSubscriber\DomainEventPublisher;
use Konair\HAP\Shared\Infrastructure\Domain\Model\EventStore\PDOEventStore;
use Konair\HAP\Shared\Infrastructure\SerializationHandler\CarbonIntervalSerializationHandler;
use Konair\HAP\Shared\Infrastructure\SerializationHandler\CarbonSerializationHandler;
use PDO;

return function (ContainerConfigurator $configurator) {

    /*****************************************************
     * default configuration for services in *this* file *
     *****************************************************/
    $services = $configurator->services()
        ->defaults()
        ->autowire()       // Automatically injects dependencies in your services.
        ->autoconfigure(); // Automatically registers your services as commands, event subscribers, etc.

    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Tests,Kernel.php}');
    $services
        ->load(
            'Konair\\HAP\\Payment\\Infrastructure\\Http\\Controller\\',
            '../vendor/konair/hap-payment-package/src/Infrastructure/Http/Controller/*'
        )
        ->tag('controller.service_arguments');

    /*********
     * Queue *
     *********/
    // $services->set(\PhpAmqpLib\Connection\AMQPStreamConnection::class)
    //     ->args([
    //         '%env(QUEUE_HOST)%',
    //         '%env(QUEUE_PORT)%',
    //         '%env(QUEUE_USER)%',
    //         '%env(QUEUE_PASSWORD)%',
    //     ])
    //     ->lazy();

    /************
     * DATABASE *
     ************/
    $services->set(PDO::class, PDO::class)
        ->args([
            'mysql:host=%env(MYSQL_HOST)%;port=%env(int:MYSQL_PORT)%;dbname=%env(MYSQL_DATABASE)%',
            '%env(MYSQL_USER)%',
            '%env(MYSQL_PASSWORD)%',
        ])
        ->lazy();

    $services->set(EventStore::class, PDOEventStore::class)
        ->args([service(PDO::class), service(SerializerInterface::class), 'payment_domain_events']);

    $services->set(CartRepository::class, EventStoreCartRepository::class);
    $services->set(BillingDataRepository::class, EventStoreBillingDataRepository::class);
    $services->set(PricePlanRepository::class, EventStorePricePlanRepository::class);

    /**************************
     * Domain event publisher *
     **************************/
    $services->set(DomainEventPublisher::class)
        ->factory([DomainEventPublisher::class, 'instance']);

    /**************
     * Serializer *
     **************/
    $services->set(CarbonSerializationHandler::class)
        ->factory([CarbonSerializationHandler::class, 'create']);
    $services->set(CollectionSerializationHandler::class)
        ->factory([CollectionSerializationHandler::class, 'create']);
    $services->set(VatSerializationHandler::class)
        ->factory([VatSerializationHandler::class, 'create']);
    $services->set(AccessPlanSerializationHandler::class)
        ->factory([AccessPlanSerializationHandler::class, 'create']);
    $services->set(CarbonIntervalSerializationHandler::class)
        ->factory([CarbonIntervalSerializationHandler::class, 'create']);

    $services->set(SerializerBuilder::class)
        ->factory([SerializerBuilder::class, 'create'])
        ->call('configureHandlers', [service(CarbonSerializationHandler::class)])
        ->call('configureHandlers', [service(VatSerializationHandler::class)])
        ->call('configureHandlers', [service(CollectionSerializationHandler::class)])
        ->call('configureHandlers', [service(AccessPlanSerializationHandler::class)])
        ->call('configureHandlers', [service(CarbonIntervalSerializationHandler::class)]);

    // $services->set(CarbonSerializationHandler::class)
    //     ->tag('jms_serializer.subscribing_handler');

    $services->set(SerializerInterface::class)
        ->factory([service(SerializerBuilder::class), 'build'])
        ->lazy();

    /*******************
     * Event Listeners *
     *******************/
    $services->set(DomainEventSubscriberListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);

    $services->set(DisableAliasAnnotationListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);

    $services->set(JsonRequestTransformerListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);


    /******************
     * Other services *
     ******************/
    $services->set(PaymentProviderFactory::class);
};
