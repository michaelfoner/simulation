<?php


namespace App\Entity;


class Organism
{
    /**
     * @var int
     */
    public $x;
    /**
     * @var int
     */
    public $y;
    /**
     * @var int
     */
    public $species;

    /**
     * Organism constructor.
     * @param int $x
     * @param int $y
     * @param int $species
     */
    public function __construct(int $x,int $y,int $species)
    {
        $this->x = $x;
        $this->y = $y;
        $this->species = $species;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @param int $pos
     *
     * @return $this
     */
    public function setX(int $pos): self
    {
        $this->x = $pos;

        return $this;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param int $pos
     *
     * @return $this
     */
    public function setY(int $pos): self
    {
        $this->y = $pos;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpecies(): int
    {
        return $this->species;
    }

    /**
     * @param int $species
     *
     * @return $this
     */
    public function setSpecies(int $species): self
    {
        $this->species = $species;

        return $this;
    }
}