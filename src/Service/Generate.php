<?php


namespace App\Service;


use App\Entity\World;
use App\Service\EvolutionRules\BornOrganism;
use App\Service\EvolutionRules\DieFromOvercrowding;
use App\Service\EvolutionRules\DieFromStarvation;
use App\Service\EvolutionRules\Live;
use App\Service\Parser\OrganismRule;
use App\Service\WorldParser\ConsoleWriter;
use App\Service\WorldParser\Reader;
use App\Service\WorldParser\FileWriter;

class Generate
{
    /** @var string */
    const WORLD_TEMPLATE = 'world.xml';

    /** @var string */
    const GUN_TEMPLATE = 'gun.xml';

    /** @var string */
    const OUT_TEMPLATE = 'out.xml';

    /** @var string */
    const TEMPLATE_PATH = __DIR__ . '/../../templates/resources/';

    /** @var string */
    const GUN = 'gun';

    /** @var int */
    const DEFAULT_MICRO_TIME = 3;

    /** @var int */
    const MIN_TIME = 3;

    /** @var int */
    const CONST_TIME = 100;

    /**
     * @var Reader
     */
    private $worldReader;
    /**
     * @var FileWriter
     */
    private $worldWriter;
    /**
     * @var int
     */
    private $debounce;
    /**
     * @var int
     */
    private $mininumTime;
    /**
     * @var int
     */
    private $pause;
    /**
     * @var ConsoleWriter
     */
    private $logger;
    /**
     * @var World
     */
    private $world;
    /**
     * @var OrganismRule
     */
    private $fromPoint;
    /**
     * @var string
     */
    private $template;

    /**
     * Generate constructor.
     * @param string|null $templateType
     */
    public function __construct
    (
        string $templateType = null
    )
    {
        $this->template = ! empty($templateType) && $templateType === self::GUN ? self::GUN_TEMPLATE : self::WORLD_TEMPLATE;
        $this->worldReader = new Reader(self::TEMPLATE_PATH.$this->template);
        $this->worldWriter = new FileWriter(self::TEMPLATE_PATH.self::OUT_TEMPLATE);
        $this->debounce = self::DEFAULT_MICRO_TIME;
        $this->mininumTime = self::MIN_TIME;
        $this->pause = $this->calculatePause();
        $this->logger = new ConsoleWriter($this->debounce, $this->worldReader->getIterationsCount());
        $this->world = new World();
        $this->fromPoint = new OrganismRule();
    }



    /**
     * Process generate world schema
     */
    public function generate(): void
    {
        $this->parseTime();

        $evolutionRules = [
            new DieFromStarvation(),
            new DieFromOvercrowding(),
            new Live(),
            new BornOrganism(),
        ];

        $this->world->load($this->worldReader);
        $simulation = new ParseWorld($this->fromPoint, $evolutionRules);
        $logger = $this->logger;
        $pause = $this->pause;
        $simulation->loopWorld(
            $this->world,
            $this->worldReader->getIterationsCount(),
            function ($world, $iterationsLeft) use ($logger, $pause) {
                $world->save($logger, $iterationsLeft);
                if ($pause) {
                    usleep($pause);
                }
            });

        $this->world->save($this->worldWriter, $this->worldReader->getIterationsCount());
    }

    /**
     * Parse pause time
     */
    private function parseTime()
    {
        if ($this->pause > $this->debounce) {
            $this->debounce = null;
        }
        if ($this->pause <= 0) {
            $this->pause = null;
        }
    }

    /**
     * @return int
     */
    private function calculatePause(): int
    {
        return (int)
            (
                ($this->mininumTime * self::CONST_TIME- $this->debounce * $this->worldReader->getIterationsCount()) / $this->worldReader->getIterationsCount()
            );
    }

}