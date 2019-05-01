<?php


namespace App\Service\WorldParser\Interfaces;


interface WorldReaderInterface
{
    /**
     * @return int
     */
    function getIterationsCount(): int ;

    /**
     * @return int
     */
    function getSpeciesCount(): int ;

    /**
     * @return  array
     */
    function getOrganismsList(): array ;

    /**
     * @return int
     */
    function getHeight(): int;

    /**
     * @return int
     */
    function getWidth(): int ;
}