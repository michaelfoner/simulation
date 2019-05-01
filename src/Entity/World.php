<?php


namespace App\Entity;

use App\Service\WorldParser\Interfaces\WorldReaderInterface;
use App\Service\WorldParser\Interfaces\WorldWriterInterface;
use InvalidArgumentException;
use LogicException;

class World
{
    /**
     * @var  array
     */
    private $cells;
    /**
     * @var bool
     */
    private $initialized = false;
    /**
     * @var int
     */
    private $numberOfSpecies;
    /**
     * @var int
     */
    private $height;
    /**
     * @var int
     */
    private $width;

    /**
     * @param int $x
     * @param int $y
     * @return int
     */
    public function getWorld(int $x, int $y): int
    {
        $this->checkInitialized();
        $this->checkPosition($x, $y);

        return $this->cells[$y][$x];
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $type
     */
    public function setWorld(int $x, int $y, int $type): void
    {
        $this->checkInitialized();
        $this->checkPosition($x, $y);
        $this->checkSpeciesType($type);
        $this->cells[$y][$x] = $type;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        $this->checkInitialized();

        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth():int
    {
        $this->checkInitialized();

        return $this->width;
    }

    /**
     * @param array $organisms
     * @param int $width
     * @param int $height
     * @param int $species
     */
    public function init(array $organisms, int $width, int $height, int $species): void
    {
        foreach ([$width, $height, $species] as $value) {
            $this->checkValue($value);
        }

        $this->width = $width;
        $this->height = $height;
        $this->numberOfSpecies = $species;
        $this->cells = array_fill(0, $height, array_fill(0, $width, 0));
        foreach ($organisms as $i => $organism) {
            $this->checkOrganism($organism, $i);
        }

        $this->initialized = true;
    }

    /**
     * @param  WorldReaderInterface  $source
     */
    public function load(WorldReaderInterface $source)
    {
        $this->init(
            $source->getOrganismsList(),
            $source->getWidth(),
            $source->getHeight(),
            $source->getSpeciesCount()
        );
    }

    /**
     * @param WorldWriterInterface $destination
     * @param int $numberOfIterations
     */
    public function save(WorldWriterInterface $destination,int $numberOfIterations): void
    {
        $this->checkInitialized("Cannot save uninitialized world");
        $organisms = [];
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->cells[$y][$x] > 0) {
                    $organisms[] = new Organism($x, $y, $this->cells[$y][$x]);
                }
            }
        }

        $this->write($destination, $organisms, $numberOfIterations);

    }

    /**
     * @param WorldWriterInterface $destination
     * @param array $organisms
     * @param int $numberOfIterations
     */
    private function write(WorldWriterInterface $destination,array $organisms,int $numberOfIterations): void
    {
        $destination->writeToFile(
            $organisms,
            $this->width,
            $this->height,
            $numberOfIterations,
            $this->numberOfSpecies
        );
    }

    /**
     * @param string|null $message
     */
    private function checkInitialized(string $message = null): void
    {
        if ( ! $this->initialized) {
            throw new LogicException($message ? : "Cannot access uninitialized world");
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @param string|null $message
     */
    private function checkPosition(int $x,int $y,string $message = null): void
    {
        $axes = ['X', 'Y'];
        $size = [$this->width, $this->height];
        $point = [$x, $y];
        for ($i = 0; $i < 2; $i++) {
            $position = $point[$i];
            $this->checkInvalidPosition($position, $size, $i, $axes, $message);
        }
    }

    /**
     * @param int $position
     * @param array $size
     * @param int $i
     * @param array $axes
     * @param string|null $message
     */
    private function checkInvalidPosition(int $position, array $size, int $i, array $axes, string $message = null): void
    {
        if ($position < 0 || $position >= $size[$i]) {
            $bound = $size[$i] - 1;
            $letter = $axes[$i];
            $message = $message ? : "Invalid position";
            throw new InvalidArgumentException("$message, allowed $letter range is [0..$bound], got $position");
        }
    }

    /**
     * @param int $type
     * @param string|null $message
     */
    private function checkSpeciesType(int $type,string $message = null): void
    {
        if ($type < 0 || $type > $this->numberOfSpecies) {
            $message = $message ? : "Invalid species type";
            $value = $this->numberOfSpecies > 1 ? "range is [1..{$this->numberOfSpecies}]" : "value is 1";
            throw new InvalidArgumentException("$message, allowed $value");
        }
    }


    /**
     * @param Organism $organism
     * @param int $i
     */
    private function checkOrganism(Organism $organism, int $i): void
    {
        if ( ! $organism instanceof Organism) {
            throw new InvalidArgumentException("Argument 1 must be array of Organism objects");
        }

        $this->checkPosition($organism->x, $organism->y, "Organism #$i has invalid position");
        if ($organism->species === 0) {
            throw new InvalidArgumentException("Organism #$i species type must be positive integer");
        }

        $this->checkSpeciesType($organism->species, "Organism #$i has invalid species type");
        if ($this->cells[$organism->y][$organism->x] !== 0) {
            throw new InvalidArgumentException("Organism #$i has a position that is already occupied");
        }

        $this->cells[$organism->y][$organism->x] = $organism->species;
    }

    /**
     * @param int $value
     */
    private function checkValue(int $value): void
    {
        if ( ! is_int($value) || $value <= 0) {
            throw new InvalidArgumentException("Arguments 2 to 4 must be positive integers");
        }
    }
}