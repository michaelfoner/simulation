<?php

namespace App\Service\EvolutionRules;

use App\Service\EvolutionRules\Interfaces\RulesInterface;

class DieFromOvercrowding implements RulesInterface
{
    /**
     * @var int
     */
    private $maxUserBeforeOvercroweded;

    /**
     * DieFromOvercrowding constructor.
     * @param int $maxUserBeforeOvercroweded
     */
    public function __construct(int $maxUserBeforeOvercroweded = 3)
    {
        $this->maxUserBeforeOvercroweded = $maxUserBeforeOvercroweded;
    }

    /**
     * @param $currentOccupant
     * @param array $neighborCounts
     *
     * @return bool|int
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        $sameTypeNeighborsCount = isset($neighborCounts[$currentOccupant])
            ? $neighborCounts[$currentOccupant]
            : 0
        ;

        if ($currentOccupant && $sameTypeNeighborsCount > $this->maxUserBeforeOvercroweded) {
            return 0;
        }
        return FALSE;
    }
}