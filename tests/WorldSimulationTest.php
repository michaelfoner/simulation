<?php

namespace App\Tests;

use App\Entity\World;
use App\Service\EvolutionRules\BornOrganism;
use App\Service\EvolutionRules\DieFromOvercrowding;
use App\Service\EvolutionRules\DieFromStarvation;
use App\Service\EvolutionRules\Live;
use App\Service\Parser\Interfaces\CounterInterface;
use App\Service\Parser\OrganismRule;
use App\Service\ParseWorld;
use InvalidArgumentException;
use ReflectionMethod;

class WorldSimulationTest extends WorldTest
{
    /** @var array */
    const ITERATE = [
        [100, false],
        [100, true],
    ];

    /** @var array */
    const INVALID_WORLD = [
            [0],
            [-1],
            [3.14],
            ['fifty five'],
        ];

    /** @var array */
    const LOOP_WORLD = [
        [
            5, 3,
            [
                [0, 0, null],
                [1, 0, 0],
                [2, 0, 1],
                [3, 0, null],
                [4, 0, null],
                [0, 1, 0],
                [1, 1, 2],
                [2, 1, null],
                [3, 1, null],
                [4, 1, null],
                [0, 2, null],
                [1, 2, 1],
                [2, 2, null],
                [3, 2, null],
                [4, 2, null],
            ],
            [
                [1, 0, 0],
                [2, 0, 1],
                [0, 1, 0],
                [1, 1, 2],
                [1, 2, 1],
            ],
        ],
    ];

    /** @var array */
    const EVOLVE_WORLD = [
        [0, 0, 1, [0 => 2], 0],
        [0, 1, 0, [0 => 1, 1 => 2], null],
        [0, 1, 1, [0 => 1, 1 => 2], null],
        [0, 1, 2, [0 => 1, 1 => 2], 0],
        [1, 1, 1, [1 => 4], 0],
        [1, 1, 1, [1 => 1], 0],
        [1, 1, 1, [1 => 2], null],
        [1, 1, 1, [1 => 3], null],
        [1, 1, 0, [0 => 4], null],
        [1, 1, 0, [1 => 3], 1],
        [1, 1, 0, [1 => 2, 2 => 2], null],
        [1, 1, 0, [1 => 3, 2 => 3], 1],
        [1, 1, 0, [1 => 4], null],
    ];

    /**
     * @var  array
     */
    protected static $worldSpace = [
        [2, 2, 0, 0, 1],
        [2, 0, 1, 1, 0],
        [0, 1, 1, 1, 0],
        [0, 1, 1, 0, 3],
        [1, 0, 0, 3, 3],
    ];

    /**
     * @param array $cells
     * @param int $iterations
     * @param array $expected
     *
     * @dataProvider  getWorldSimulationData
     *
     * @throws \ReflectionException
     */
    public function testWorldSimulation(array $cells, int $iterations, array $expected): void
    {
        $world = new World();
        $this->getProperty($world, 'cells')
            ->setValue($world, $cells);
        $this->getProperty($world, 'height')
            ->setValue($world, count($cells));
        $this->getProperty($world, 'width')
            ->setValue($world, $cells ? count($cells[0]) : 0);
        $this->getProperty($world, 'numberOfSpecies')
            ->setValue($world, max(array_map(function ($row) { return max($row); }, $cells)));
        $this->getProperty($world, 'initialized')
            ->setValue($world, true);
        $evolutionRules = $this->createEvolutionRules($this->any());
        $object = $this->createParseWorldObject(new OrganismRule(), $evolutionRules);
        $object->loopWorld($world, $iterations);
        $actual = $this->getProperty($world, 'cells')
            ->getValue($world);
        $this->assertEquals($expected, $actual);
    }


    /**
     * @param int $numberOfIterations
     * @param bool $withCallback
     *
     * @dataProvider  getDataForLoop
     *
     * @throws \ReflectionException
     */
    public function testLoopWorld(int $numberOfIterations, bool $withCallback)
    {
        $world = $this->createMock(World::class);
        $object = $this->getMockBuilder(ParseWorld::class)
            ->setMethodsExcept(['loopWorld'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->exactly($numberOfIterations))
            ->method('loop')
            ->with($world);
        $callback = null;
        if ($withCallback) {
            $onIterationSpy = $this->getMockBuilder('stdClass')
                ->setMethods(['onIteration'])
                ->getMock();
            $onIterationSpy->expects($this->exactly($numberOfIterations))
                ->method('onIteration')
                ->with($world);
            $callback = [$onIterationSpy, 'onIteration'];
        }
        $object->loopWorld($world, $numberOfIterations, $callback);
    }


    /**
     * @param $iteration
     *
     * @dataProvider  getInvalidLoopData
     *
     * @throws \ReflectionException
     */
    public function testWorldWithInvalidIteration($iteration): void
    {
        $object = $this->createParseWorldObject($this->createMock(CounterInterface::class));
        $world = $this->createMock(World::class);
        $this->expectException(InvalidArgumentException::class);
        $object->loopWorld($world, 0,$iteration);
    }


    /**
     * @param $width
     * @param $height
     * @param $evolveCalls
     * @param $setAtCalls
     *
     * @dataProvider  getLoopWorldData
     *
     * @throws \ReflectionException
     */
    public function testLoopServer($width, $height, $evolveCalls, $setAtCalls): void
    {
        $world = $this->createMock(World::class);
        $world->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue($width));
        $world->expects($this->any())
            ->method('getHeight')
            ->will($this->returnValue($height));
        $object = $this->getMockBuilder(ParseWorld::class)
            ->disableOriginalConstructor()
            ->setMethods(['evolveWorld'])
            ->getMock();
        for ($i = 0; $i < count($evolveCalls); $i++) {
            list ($x, $y, $type) = $evolveCalls[$i];
            $object->expects($this->at($i))
                ->method('evolveWorld')
                ->with($world, $x, $y)
                ->will($this->returnValue($type));
        }
        for ($i = 0; $i < count($setAtCalls); $i++) {
            list ($x, $y, $type) = $setAtCalls[$i];
            // First two calls were to get width and height.
            $world->expects($this->at($i + 2))
                ->method('setWorld')
                ->with($x, $y, $type);
        }
        $object->loop($world);
    }


    /**
     * @dataProvider  getEvolutionWorldData
     */
    public function testEvolve($x, $y, $type, $neighborCounts, $expected)
    {
        $world = $this->createMock(World::class);
        $world->expects($this->any())
            ->method('getWorld')
            ->with($x, $y)
            ->will($this->returnValue($type));
        $neighborsLocator = $this->createMock(CounterInterface::class);
        $neighborsLocator->expects($this->once())
            ->method('getCount')
            ->with($world, $x, $y)
            ->will($this->returnValue($neighborCounts));
        $evolutionRules = $this->createEvolutionRules(
            $type === 0 && ! empty($expected) ? $this->once() : $this->never()
        );
        $object = $this->createParseWorldObject($neighborsLocator, $evolutionRules);
        $actual = $this->getObjectMethod($object, 'evolveWorld')
            ->invoke($object, $world, $x, $y);
        $this->assertSame($expected, $actual);
    }


    /**
     * @return array
     */
    public function getLoopWorldData(): array
    {
        return self::LOOP_WORLD;
    }

    /**
     * @return array
     */
    public function getInvalidLoopData(): array
    {
        return self::INVALID_WORLD;
    }

    /**
     * @return array
     */
    public function getDataForLoop(): array
    {
        return self::ITERATE;
    }

    /**
     * @return array
     */
    public function getWorldSimulationData(): array
    {
        return [
            [
                self::$worldSpace, 1,
                [
                    [2, 2, 0, 1, 0],
                    [2, 2, 0, 0, 1],
                    [0, 0, 0, 0, 0],
                    [1, 0, 0, 1, 3],
                    [0, 1, 0, 3, 3],
                ],
            ],
            [
                self::$worldSpace, 2,
                [
                    [2, 2, 0, 0, 0],
                    [2, 2, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 3],
                    [0, 0, 0, 3, 3],
                ],
            ],
            [
                self::$worldSpace, 3,
                [
                    [2, 2, 0, 0, 0],
                    [2, 2, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 3, 3],
                    [0, 0, 0, 3, 3],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getEvolutionWorldData(): array
    {
        return self::EVOLVE_WORLD;
    }

    /**
     * @param $object
     * @param $name
     *
     * @return ReflectionMethod
     *
     * @throws \ReflectionException
     */
    protected function getObjectMethod($object, $name): ReflectionMethod
    {
        $method = new ReflectionMethod($object, $name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param $bornExpectation
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private function createEvolutionRules($bornExpectation): array
    {
        $bornRules = $this->getMockBuilder(BornOrganism::class)
            ->setMethods(['resolveBirthRights'])
            ->getMock();
        $bornRules->expects($bornExpectation)
            ->method('resolveBirthRights')
            ->will($this->returnCallback(function (array $elements) {
                return reset($elements);
            }));
        return [
            new DieFromStarvation(),
            new DieFromOvercrowding(),
            new Live(),
            $bornRules,
        ];
    }

    /**
     * @param CounterInterface $counter
     * @param array $evolutionRules
     *
     * @return ParseWorld
     */
    private function createParseWorldObject(CounterInterface $counter, array $evolutionRules = []): ParseWorld
    {
        return new ParseWorld($counter, $evolutionRules);
    }
}
