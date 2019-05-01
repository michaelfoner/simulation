<?php

namespace App\Tests;

use App\Entity\Organism;
use App\Entity\World;
use App\Service\WorldParser\Interfaces\WorldReaderInterface;
use InvalidArgumentException;
use LogicException;
use ReflectionProperty;

class WorldSpaceTest extends WorldTest
{
    /**
     * @param $cells
     * @param $init
     * @param $x
     * @param $y
     * @param $expected
     *
     * @dataProvider  getDataWorld
     *
     * @throws \ReflectionException
     */
    public function testGetWorld($cells, $init, $x, $y, $expected): void
    {
        $worldObject = $this->createWorldObject($cells, null, $init);
        $this->exceptionFromClass($expected);
        $actual = $worldObject->getWorld($x, $y);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider  getSetWorldData
     */
    public function testSetWorld($cells, $species, $init, $x, $y, $value, $expected): void
    {
        $worldObject = $this->createWorldObject($cells, $species, $init);
        $this->exceptionFromClass($expected);
        $worldObject->setWorld($x, $y, $value);
        $actualCells = $this->getProperty($worldObject, 'cells')
            ->getValue($worldObject);
        $this->assertSame($expected, $actualCells[$y][$x]);
    }

    /**
     * @dataProvider  provideGetDimension
     */
    public function testGetDimension($value, $property, $function): void
    {
        $object = $this->createWorldObject(null, null, true);
        $this->getProperty($object, $property)
            ->setValue($object, $value);
        $actual = call_user_func([$object, $function]);
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider  getInitData
     */
    public function testInit($organisms, $width, $height, $species, $objectClass)
    {
        $worldObject = $this->createWorldObject();
        $this->exceptionFromClass($objectClass);
        $worldObject->init($organisms, $width, $height, $species);
        $actualCells = $this->getProperty($worldObject, 'cells')
            ->getValue($worldObject);
        $actualWorldWidth = $this->getProperty($worldObject, 'width')
            ->getValue($worldObject);
        $actualWorldHeight = $this->getProperty($worldObject, 'height')
            ->getValue($worldObject);
        $actualNumberOfSpecies = $this->getProperty($worldObject, 'numberOfSpecies')
            ->getValue($worldObject);
        $actualInitialized = $this->getProperty($worldObject, 'initialized')
            ->getValue($worldObject);
        $this->assertSame($width, count($actualCells[0]), "World width by cell rows count");
        $this->assertSame($height, count($actualCells), "World height by cell rows count");
        $this->assertSame($width, $actualWorldWidth, "World width cached value");
        $this->assertSame($height, $actualWorldHeight, "World height cached value");
        $this->assertSame($species, $actualNumberOfSpecies, "Number of species types");
        $this->assertTrue($actualInitialized, "Initialized state");
        $getExpectedOrganismType = function ($x, $y) use ($organisms) {
            foreach ($organisms as $organism) {
                if ($organism->x === $x && $organism->y === $y) {
                    return $organism->species;
                }
            }
            return 0;
        };
        foreach ($actualCells as $y => $rowOfCells) {
            foreach ($rowOfCells as $x => $actual) {
                $expected = $getExpectedOrganismType($x, $y);
                $this->assertSame($expected, $actual, "Cell ($x, $y) value");
            }
        }
    }

    /**
     * @dataProvider  getLoadData
     */
    public function testLoad($organisms, $width, $height, $species): void
    {
        $worldObject = $this->getMockBuilder(World::class)
            ->setMethodsExcept(['load'])
            ->getMock();
        $worldObject->expects($this->once())
            ->method('init')
            ->with($organisms, $width, $height, $species);
        $reader = $this->createWorldReader($organisms, $width, $height, $species);
        $worldObject->load($reader);
    }

    /**
     * @return array
     */
    public function getDataWorld(): array
    {
        return [
            '0,0' => [self::$worldSpace, TRUE, 0, 0, 0],
            '0,4' => [self::$worldSpace, TRUE, 0, 4, 1],
            '2,2' => [self::$worldSpace, TRUE, 2, 2, 1],
            'UninitializedWorld' => [self::$worldSpace, FALSE, 0, 0, new LogicException()],
            'TooLowXPosition' => [self::$worldSpace, TRUE, -1, 0, new InvalidArgumentException],
            'TooHighXPosition' => [self::$worldSpace, TRUE, 5, 0, new InvalidArgumentException],
            'TooLowYPosition' => [self::$worldSpace, TRUE, 0, -1, new InvalidArgumentException],
            'TooHighYPosition' => [self::$worldSpace, TRUE, 0, 5, new InvalidArgumentException],
        ];
    }

    /***
     * @return array
     */
    public function getSetWorldData(): array
    {
        return [
            '0,0' => [self::$worldSpace, 1, TRUE, 0, 0, 1, 1],
            '0,4' => [self::$worldSpace, 1, TRUE, 0, 4, 1, 1],
            '2,2' => [self::$worldSpace, 1, TRUE, 2, 2, 0, 0],
            'UninitializedWorld' => [self::$worldSpace, 1, FALSE, 0, 0, 1, new LogicException],
            'TooLowXPosition' => [self::$worldSpace, 1, TRUE, -1, 0, 1, new InvalidArgumentException],
            'TooHighXPosition' => [self::$worldSpace, 1, TRUE, 5, 0, 1, new InvalidArgumentException],
            'TooLowYPosition' => [self::$worldSpace, 1, TRUE, 0, -1, 1, new InvalidArgumentException],
            'TooHighYPosition' => [self::$worldSpace, 1, TRUE, 0, 5, 1, new InvalidArgumentException],
            'TooLowSpeciesNumber' => [self::$worldSpace, 1, TRUE, 2, 2, -1, new InvalidArgumentException],
            'TooHighSpeciesNumber' => [self::$worldSpace, 1, TRUE, 2, 2, 2, new InvalidArgumentException],
        ];
    }

    /**
     * @return array
     */
    public function provideGetDimension(): array
    {
        return [
            [20, 'height', 'getHeight'],
            [30, 'width', 'getWidth'],
        ];
    }

    /**
     * @return array
     */
    public function getInitData(): array
    {
        return [
            'Organism' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(10, 0, 2),
                    new Organism(10, 1, 3),
                ],
                15, 15, 4,
                null,
            ],
            'BadDimensions' => [
                [], 0, 100, 3, new InvalidArgumentException,
            ],
            'LowPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(-1, 0, 1),
                ],
                20, 20, 1,
                new InvalidArgumentException,
            ],
            'LowSpecies' => [
                [
                    new Organism(1, 1, 0),
                ],
                10, 10, 1,
                new InvalidArgumentException,
            ],
            'PositionIsReserved' => [
                [
                    new Organism(2, 2, 1),
                    new Organism(2, 2, 1),
                    new Organism(2, 2, 1),
                ],
                10, 10, 1,
                new InvalidArgumentException,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getLoadData(): array
    {
        return [
            [
                [
                    new Organism(1, 1, 1),
                    new Organism(2, 2, 2),
                    new Organism(3, 3, 3),
                ],
                10, 10, 3,
            ],
        ];
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

    /**
     * @param null $cells
     * @param null $numberOfSpecies
     * @param null $initialized
     *
     * @return World
     *
     * @throws \ReflectionException
     */
    private function createWorldObject($cells = null, $numberOfSpecies = null, $initialized = null)
    {
        $worldObject = new World();
        if ( ! empty($cells)) {
            $this->getProperty($worldObject, 'cells')
                ->setValue($worldObject, $cells);
            $this->getProperty($worldObject, 'height')
                ->setValue($worldObject, count($cells));
            $this->getProperty($worldObject, 'width')
                ->setValue($worldObject, $cells ? count($cells[0]) : 0);
        }
        if ( ! empty($numberOfSpecies)) {
            $this->getProperty($worldObject, 'numberOfSpecies')
                ->setValue($worldObject, $numberOfSpecies);
        }
        if ( ! empty($initialized)) {
            $this->getProperty($worldObject, 'initialized')
                ->setValue($worldObject, $initialized);
        }

        return $worldObject;
    }

    /**
     * @param $organisms
     * @param $width
     * @param $height
     * @param $species
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     *
     * @throws \ReflectionException
     */
    private function createWorldReader($organisms, $width, $height, $species)
    {
        $reader = $this->createMock(WorldReaderInterface::class);
        $reader->expects($this->once())
            ->method('getOrganismsList')
            ->will($this->returnValue($organisms));
        $reader->expects($this->once())
            ->method('getWidth')
            ->will($this->returnValue($width));
        $reader->expects($this->once())
            ->method('getHeight')
            ->will($this->returnValue($height));
        $reader->expects($this->once())
            ->method('getSpeciesCount')
            ->will($this->returnValue($species));

        return $reader;
    }
}
