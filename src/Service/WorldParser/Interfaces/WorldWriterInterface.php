<?php


namespace App\Service\WorldParser\Interfaces;


interface WorldWriterInterface
{
    /**
     * @param array $organismsList
     * @param int $width
     * @param int $height
     * @param int $iterations
     * @param int $species
     * @return mixed
     */
    public function writeToFile(array $organismsList, int $width, int $height, int $iterations, int $species);
}