<?php


namespace App\Service\EvolutionRules\Interfaces;


interface RulesInterface
{
    /**
     * @param $currentOccupant
     * @param array $neighborCounts
     */
    public function evolve($currentOccupant, array $neighborCounts);
}