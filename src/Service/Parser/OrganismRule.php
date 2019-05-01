<?php

namespace App\Service\Parser;

use App\Entity\World;

class OrganismRule extends Counter
{
    /** @var int */
    const ZERO = 0;

    /** @var int */
    const ONE = 1;

    /**
     * @param World $world
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    public function getNeighborPosition(World $world, int $x, int $y): array
    {
        $neighbors = [];
        $width = $world->getWidth();
        $height = $world->getHeight();

        if ($x > 0) {
            $neighbors[] = $world->getWorld($x - self::ONE, $y);
        }

        if ($x < $width - self::ONE) {
            $neighbors[] = $world->getWorld($x + self::ONE, $y);
        }

        if ($y > self::ZERO) {
            $neighbors[] = $world->getWorld($x, $y - self::ONE);
        }

        if ($y < $height - self::ONE) {
            $neighbors[] = $world->getWorld($x, $y + self::ONE);
        }

        if ($x > self::ZERO && $y > self::ZERO) {
            $neighbors[] = $world->getWorld($x - self::ONE, $y - self::ONE);
        }

        if ($x < $width - self::ONE && $y > self::ZERO) {
            $neighbors[] = $world->getWorld($x + self::ONE, $y - self::ONE);
        }

        if ($x > self::ZERO && $y < $height - self::ONE) {
            $neighbors[] = $world->getWorld($x - self::ONE, $y + self::ONE);
        }

        if ($x < $width - self::ONE && $y < $height - self::ONE) {
            $neighbors[] = $world->getWorld($x + self::ONE, $y + self::ONE);
        }

        return $neighbors;
    }
}