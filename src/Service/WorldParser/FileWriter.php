<?php


namespace App\Service\WorldParser;

use App\Service\WorldParser\Interfaces\WorldWriterInterface;
use InvalidArgumentException;
use SimpleXMLElement;

class FileWriter implements WorldWriterInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * XMLWorldWriter constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param array $organismsList
     * @param int $width
     * @param int $height
     * @param int $iterations
     * @param int $species
     * @return mixed|void
     */
    public function writeToFile(array $organismsList, int $width, int $height, int $iterations, int $species)
    {
        $this->chceckDimensions($width, $height);
        $this->createXMl($organismsList, $width, $iterations, $species)
            ->asXML($this->path);
    }

    /**
     * @param int $width
     * @param int $height
     */
    private function chceckDimensions(int $width, int $height): void
    {
        if ($width !== $height) {
            throw new InvalidArgumentException('XML format supports only square worlds');
        }
    }

    /**
     * @param array $organismsList
     * @param int $dimension
     * @param int $iterations
     * @param int $species
     * @return SimpleXMLElement
     */
    private function createXMl(array $organismsList, int $dimension, int $iterations, int $species): SimpleXMLElement
    {
        $xml = simplexml_load_string('<life/>');
        $world = $xml->addChild('world');
        $world->addChild('cells', $dimension);
        $world->addChild('species', $species);
        $world->addChild('iterations', $iterations);
        $organisms = $xml->addChild('organisms');
        $this->addOrganismToXML($organisms, $organismsList);

        return $xml;
    }

    /**
     * @param SimpleXMLElement $organisms
     * @param array $organismsList
     */
    private function addOrganismToXML(SimpleXMLElement $organisms,array $organismsList): void
    {
        foreach ($organismsList as $organismItem) {
            $organism = $organisms->addChild('organism');
            $organism->addChild('x_pos', $organismItem->x);
            $organism->addChild('y_pos', $organismItem->y);
            $organism->addChild('species', $organismItem->species);
        }
    }
}