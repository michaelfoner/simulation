<?php


namespace App\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

abstract class WorldTest extends TestCase
{
    /**
     * @var  array
     */
    protected static $worldSpace = [
        [0, 0, 0, 0, 0],
        [0, 1, 0, 0, 0],
        [0, 1, 1, 0, 0],
        [0, 1, 0, 1, 0],
        [1, 1, 0, 1, 1],
    ];

    /**
     * @param $objectClass
     */
    protected function exceptionFromClass($objectClass)
    {
        if ($objectClass instanceof Exception) {
            $this->expectException(get_class($objectClass));
        }
    }

    /**
     * @param $class
     * @param $propertyName
     *
     * @return ReflectionProperty
     *
     * @throws \ReflectionException
     */
    protected function getProperty($class, $propertyName): ReflectionProperty
    {
        $property = new ReflectionProperty($class, $propertyName);
        $property->setAccessible(true);

        return $property;
    }
}