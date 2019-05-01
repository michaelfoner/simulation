<?php

namespace App\Service\EvolutionRules;

use App\Service\EvolutionRules\Interfaces\RulesInterface;

class Live implements RulesInterface
{
    /**
     * @param $currentOccupant
     * @param array $neighborCounts
     *
     * @return bool|null
     */
    public function evolve($currentOccupant, array $neighborCounts): ?bool
    {
        return $currentOccupant
            ?  null
            : false
        ;
    }
}