<?php


namespace App\Service\Parser\Interfaces;


use App\Entity\World;

interface CounterInterface
{
    /**
     * @param World $world
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    function getCount(World $world, int $x, int $y): array ;

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    function getNeighborPosition(World $world, int $x, int $y): array ;
}