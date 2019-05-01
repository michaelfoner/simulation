<?php


namespace App\Service\EvolutionRules;

use App\Service\EvolutionRules\Interfaces\RulesInterface;

class DieFromStarvation implements RulesInterface
{
    /**
     * @var int
     */
    private $minimalToSurvive;

    /**
     * DieFromStarvation constructor.
     * @param int $minimalToSurvive
     */
    public function __construct(int $minimalToSurvive = 2)
    {
        $this->minimalToSurvive = $minimalToSurvive;
    }

    /**
     * @param $currentOccupant
     * @param array $neighborCounts
     *
     * @return bool|int
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        $evolve = 0;
        $sameTypeNeighborsCount = isset($neighborCounts[$currentOccupant])
            ? $neighborCounts[$currentOccupant]
            : $evolve
        ;

        if ($currentOccupant && $sameTypeNeighborsCount < $this->minimalToSurvive) {
            return $evolve;
        }

        return false;
    }
}