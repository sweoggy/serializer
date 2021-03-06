<?php

declare(strict_types=1);

namespace JMS\Serializer\Tests\Metadata\Driver;

use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\Driver\NullDriver;

class NullDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsValidMetadata()
    {
        $driver = new NullDriver();

        $metadata = $driver->loadMetadataForClass(new \ReflectionClass('stdClass'));

        self::assertInstanceOf(ClassMetadata::class, $metadata);
        self::assertCount(0, $metadata->propertyMetadata);
    }
}
