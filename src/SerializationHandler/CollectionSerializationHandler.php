<?php

namespace App\SerializationHandler;

use Closure;
use Exception;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Konair\HAP\Payment\Domain\Model\Price\PricePart;
use Konair\HAP\Payment\Domain\Model\Price\PricePartCollection;

final class CollectionSerializationHandler implements SubscribingHandlerInterface
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
                'type' => PricePartCollection::class,
                'method' => 'deserialize',
            ]
        ];
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param string[]|array[] $data
     * @param string[]|array[] $type
     * @param Context $context
     * @return PricePartCollection
     * @throws Exception
     */
    public function deserialize(
        JsonDeserializationVisitor $visitor,
        array $data,
        array $type,
        Context $context,
    ): PricePartCollection {
        $collection = new PricePartCollection();

        $elements = $data['elements'] ?? [];

        if (!is_array($elements)) {
            throw new Exception();
        }

        foreach ($elements as $element) {
            $collection = $collection->append(
                $context->getNavigator()->accept($element, ['name' => PricePart::class])
            );
        }

        return $collection;
    }
}
