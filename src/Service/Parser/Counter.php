<?php


namespace App\Service\Parser;

use App\Entity\World;
use App\Service\Parser\Interfaces\CounterInterface;

abstract class Counter implements CounterInterface
{
    /**
     * @param World $world
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    public function getCount(World $world, int $x, int $y): array
    {
        $counts = [];
        foreach ($this->getNeighborPosition($world, $x, $y) as $neighbor) {
            if ( ! isset($counts[$neighbor])) {
                $counts[$neighbor] = 0;
            }
            $counts[$neighbor]++;
        }
        return $counts;
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    abstract public function getNeighborPosition(World $world, int $x, int $y): array ;
}