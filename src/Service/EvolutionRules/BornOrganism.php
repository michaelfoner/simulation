<?php


namespace App\Service\EvolutionRules;


use App\Service\EvolutionRules\Interfaces\RulesInterface;

class BornOrganism implements RulesInterface
{
    /**
     * @var int
     */
    private $neighborsToGiveLive;

    /**
     * BornOrganism constructor.
     *
     * @param int $neighborsToGiveLive
     */
    public function __construct(int $neighborsToGiveLive = 3)
    {
        $this->neighborsToGiveLive = $neighborsToGiveLive;
    }

    /**
     * @param $currentOccupant
     * @param array $neighborCounts
     *
     * @return bool|int
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        $eligibleToGiveBirth = [];
        foreach ($neighborCounts as $type => $count) {
            if ($type && $count === $this->neighborsToGiveLive) {
                $eligibleToGiveBirth[] = $type;
            }
        }

        if ($eligibleToGiveBirth) {
            return $this->resolveBirthRights($eligibleToGiveBirth);
        }

        return FALSE;
    }

    /**
     * @param array $allEligible
     *
     * @return int
     */
    protected function resolveBirthRights(array $allEligible): int
    {
        shuffle($allEligible);
        return reset($allEligible);
    }
}