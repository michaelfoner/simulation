<?php

namespace App\Tests;

use App\Service\WorldParser\Reader;

class WorldReaderTest extends WorldTest
{
    /** @var int */
    const WORLD_ITERATION = 400000;

    /** @var int */
    const WORLD_SPECIES = 1;

    /** @var int */
    const WORLD_DIMENSIONS = 48;

    /** @var string */
    const TEMPLATE_PATH = __DIR__ . '/../templates/resources/';

    /**
     * @param string $file
     * @param int $expected
     *
     * @dataProvider  worldIterationsCount
     */
    public function testIterations(string $file, int $expected): void
    {
        $object = $this->createReader($file);
        $actual = $object->getIterationsCount();
        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $file
     * @param int $expected
     *
     * @dataProvider  worldSpeciesCount
     */
    public function testWorldSpecies(string $file,int $expected): void
    {
        $object = $this->createReader($file);
        $actual = $object->getSpeciesCount();
        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $file
     * @param int $expected
     *
     * @dataProvider  worldDimensions
     */
    public function testWorldDimensions(string $file,int $expected): void
    {
        $object = $this->createReader($file);
        $actualWidth = $object->getWidth();
        $actualHeight = $object->getHeight();
        $this->assertSame($expected, $actualWidth);
        $this->assertSame($expected, $actualHeight);
    }

    /**
     * @return array
     */
    public function worldSpeciesCount(): array
    {
        return [
            ['world.xml', self::WORLD_SPECIES],
            ['gun.xml', self::WORLD_SPECIES],
        ];
    }

    /**
     * @return array
     */
    public function worldIterationsCount(): array
    {
        return [
            ['world.xml', self::WORLD_ITERATION],
            ['gun.xml', self::WORLD_ITERATION],
        ];
    }

    /**
     * @return array
     */
    public function worldDimensions(): array
    {
        return [
            ['world.xml', self::WORLD_DIMENSIONS],
            ['gun.xml', self::WORLD_DIMENSIONS],
        ];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getWorldFilePath(string $file): string
    {
        return self::TEMPLATE_PATH.$file;
    }

    /**
     * @param string $file
     *
     * @return Reader
     */
    private function createReader(string $file): Reader
    {
        $xmlPath = $this->getWorldFilePath($file);

        return new Reader($xmlPath);
    }
}