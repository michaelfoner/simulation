<?php


namespace App\Service\WorldParser;

use App\Entity\Organism;
use App\Service\WorldParser\Interfaces\WorldReaderInterface;
use SimpleXMLElement;

class Reader implements WorldReaderInterface
{
    /**
     * @var  SimpleXMLElement
     */
    private $spaceFile;

    /**
     * @param  string  $filePath  Path to XML file containing a world definition.
     */
    public function __construct(string $filePath)
    {
        $this->spaceFile = simplexml_load_file($filePath);
    }

    /**
     * @return int
     */
    public function getIterationsCount(): int
    {
        return (int) $this->spaceFile->world->iterations;
    }

    /**
     * @return int
     */
    public function getSpeciesCount(): int
    {
        return (int) $this->spaceFile->world->species;
    }

    /**
     * @return  array
     */
    public function getOrganismsList(): array
    {
        $organisms = (array) $this->spaceFile->organisms;
        if ($organisms) {
            return array_map(
                function (SimpleXMLElement $element) {
                    return new Organism(
                        intval($element->x_pos),
                        intval($element->y_pos),
                        intval($element->species)
                    );
                },
                $organisms['organism']
            );
        }

        return [];
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return (int) $this->getWorldDimension();
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->getWorldDimension();
    }

    /**
     * @return int
     */
    private function getWorldDimension(): int
    {
        return (int) $this->spaceFile->world->cells;
    }
}