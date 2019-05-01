<?php


namespace App\Service;

use App\Entity\World;
use App\Service\Parser\Interfaces\CounterInterface;
use InvalidArgumentException;

class ParseWorld
{
    /**
     * @var array
     */
    private $evolutionRules;
    /**
     * @var CounterInterface
     */
    private $counter;

    /**
     * ParseWorld constructor.
     * @param CounterInterface $counter
     * @param array $evolutionRules
     */
    public function __construct(CounterInterface $counter, array $evolutionRules)
    {
        $this->evolutionRules = $evolutionRules;
        $this->counter = $counter;
    }

    /**
     * @param World $world
     * @param int $numberOfIterations
     * @param null $onIteration
     */
    public function loopWorld(World $world, int $numberOfIterations, $onIteration = null): void
    {
        if ( ! is_int($numberOfIterations) || $numberOfIterations <= 0) {
            throw new InvalidArgumentException('Number of iterations must be positive integer');
        }

        if ( ! empty($onIteration) && ! is_callable($onIteration)) {
            throw new InvalidArgumentException('On iteration must be callback or NULL');
        }

        do {
            $this->loop($world);
            if ( ! empty($onIteration)) {
                call_user_func($onIteration, $world, $numberOfIterations - 1);
            }
        }
        while (--$numberOfIterations > 0);
    }

    /**
     * @param World $world
     */
    public function loop(World $world): void
    {
        $width = $world->getWidth();
        $height = $world->getHeight();
        $evolution = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $type = $this->evolveWorld($world, $x, $y);
                if ($type !== null) {
                    $evolution[] = [$x, $y, $type];
                }
            }
        }

        foreach ($evolution as $change) {
            list ($x, $y, $type) = $change;
            $world->setWorld($x, $y, $type);
        }
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     * @return null
     */
    protected function evolveWorld(World $world, int $x, int $y)
    {
        $type = $world->getWorld($x, $y);
        $neighborCounts = $this->counter->getCount($world, $x, $y);
        foreach ($this->evolutionRules as $rule) {
            $outcome = $rule->evolve($type, $neighborCounts);
            if ($outcome !== false) {
                return $outcome;
            }
        }

        return null;
    }
}