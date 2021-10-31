<?php

namespace App\SerializationHandler;

use Closure;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\SkipHandlerException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Konair\HAP\Payment\Domain\Model\ItemAccessPlan\ItemAccessPlan;
use Konair\HAP\Payment\Domain\Model\ItemAccessPlan\LengthItemAccessPlan;
use Konair\HAP\Payment\Domain\Model\ItemAccessPlan\LifetimeItemAccessPlan;
use Konair\HAP\Payment\Domain\Model\ItemAccessPlan\PeriodicItemAccessPlan;

final class AccessPlanSerializationHandler implements SubscribingHandlerInterface
{
    public static function create(): Closure
    {
        return function (HandlerRegistry $registry) {
            $registry->registerSubscribingHandler(new self());
        };
    }

    /**
     * @return array[]
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => ItemAccessPlan::class,
                'method' => 'deserialize',
            ]
        ];
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param string[] $data
     * @param string[]|array[] $type
     * @param Context $context
     * @return ItemAccessPlan
     */
    public function deserialize(
        JsonDeserializationVisitor $visitor,
        array $data,
        array $type,
        Context $context
    ): ItemAccessPlan {
        return match ($data['name']) {
            'lifetime' => $context->getNavigator()->accept($data, ['name' => LifetimeItemAccessPlan::class]),
            'length' => $context->getNavigator()->accept($data, ['name' => LengthItemAccessPlan::class]),
            'periodic' => $context->getNavigator()->accept($data, ['name' => PeriodicItemAccessPlan::class]),
            default => throw new SkipHandlerException(),
        };
    }
}
