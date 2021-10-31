<?php

namespace App\SerializationHandler;

use Closure;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\SkipHandlerException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Konair\HAP\Payment\Domain\Model\Price\ValueObject\HungarianVat;
use Konair\HAP\Payment\Domain\Model\Price\Vat;

final class VatSerializationHandler implements SubscribingHandlerInterface
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
                'type' => Vat::class,
                'method' => 'deserialize',
            ]
        ];
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param int[]|string[] $data
     * @param string[]|array[] $type
     * @param Context $context
     * @return Vat
     */
    public function deserialize(JsonDeserializationVisitor $visitor, array $data, array $type, Context $context): Vat
    {
        return match ($data['country_code']) {
            348 => $context->getNavigator()->accept($data, ['name' => HungarianVat::class]),
            default => throw new SkipHandlerException(),
        };
    }
}
