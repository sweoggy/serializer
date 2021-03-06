<?php

declare(strict_types=1);

namespace JMS\Serializer\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

final class ArrayCollectionHandler implements SubscribingHandlerInterface
{
    /**
     * @var bool
     */
    private $initializeExcluded = true;

    public function __construct($initializeExcluded = true)
    {
        $this->initializeExcluded = $initializeExcluded;
    }

    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];
        $collectionTypes = [
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection',
        ];

        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serializeCollection',
                ];

                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeCollection',
                ];
            }
        }

        return $methods;
    }

    public function serializeCollection(SerializationVisitorInterface $visitor, Collection $collection, array $type, SerializationContext $context)
    {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';

        $context->stopVisiting($collection);

        if ($this->initializeExcluded === false) {
            $exclusionStrategy = $context->getExclusionStrategy();
            if ($exclusionStrategy !== null && $exclusionStrategy->shouldSkipClass($context->getMetadataFactory()->getMetadataForClass(\get_class($collection)), $context)) {
                $context->startVisiting($collection);

                return $visitor->visitArray([], $type, $context);
            }
        }
        $result = $visitor->visitArray($collection->toArray(), $type);

        $context->startVisiting($collection);
        return $result;
    }

    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context)
    {
        // See above.
        $type['name'] = 'array';

        return new ArrayCollection($visitor->visitArray($data, $type));
    }
}
