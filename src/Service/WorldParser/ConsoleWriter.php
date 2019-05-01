<?php


namespace App\Service\WorldParser;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;
use Bramus\Ansi\Writers\BufferWriter;
use App\Service\WorldParser\Interfaces\WorldWriterInterface;

class ConsoleWriter implements WorldWriterInterface
{
    /**
     * @var  array
     */
    private $color = [
        SGR::COLOR_BG_BLACK,
        SGR::COLOR_BG_WHITE,
        SGR::COLOR_BG_RED,
        SGR::COLOR_BG_GREEN,
        SGR::COLOR_BG_YELLOW,
        SGR::COLOR_BG_BLUE,
        SGR::COLOR_BG_PURPLE,
        SGR::COLOR_BG_CYAN,
        SGR::COLOR_BG_WHITE_BRIGHT,
        SGR::COLOR_BG_RED_BRIGHT,
        SGR::COLOR_BG_GREEN_BRIGHT,
        SGR::COLOR_BG_YELLOW_BRIGHT,
        SGR::COLOR_BG_BLUE_BRIGHT,
        SGR::COLOR_BG_PURPLE_BRIGHT,
        SGR::COLOR_BG_CYAN_BRIGHT,
    ];
    /**
     * @var int
     */
    private $colorsCount;
    /**
     * @var float|int
     */
    private $pushOff;
    /**
     * @var array
     */
    private $printFunction;
    /**
     * @var int|null
     */
    private $totalIterations;

    /**
     * ConsoleWriter constructor.
     * @param int|null $pushOff
     * @param int|null $totalIterations
     */
    public function __construct(int $pushOff = null,int $totalIterations = null)
    {
        $this->ansi = new Ansi(new BufferWriter);
        $this->colorsCount = count($this->color);
        $this->pushOff = $pushOff / 1000000;
        $this->printFunction = [$this, ! empty($pushOff) ? 'debouncePrint' : 'doPrint'];
        $this->totalIterations = $totalIterations;
    }

    /**
     * @param array $organismsList
     * @param int $width
     * @param int $height
     * @param int $iterations
     * @param int $species
     * @return mixed|void
     */
    public function writeToFile(array $organismsList, int $width, int $height, int $iterations, int $species)
    {
        call_user_func($this->printFunction, $organismsList, $width, $height, $iterations, $species);
    }

    /**
     * @param array $organisms
     * @param int $width
     * @param int $height
     * @param int $iterations
     * @param int $species
     * @return int|mixed
     * @throws \Exception
     */
    protected function debouncePrint(array $organisms, int $width, int $height, int $iterations, int $species)
    {
        static $time;
        $now = microtime(true);
        $delta = isset($time) ? $now - $time : 0;

        if (empty($time) || $delta > $this->pushOff || $iterations === 0) {
            $time = $now;
            $this->doPrint($organisms, $width, $height, $iterations, $species);
        }

        return $delta;
    }

    /**
     * @param array $organisms
     * @param int $worldWidth
     * @param int $worldHeight
     * @param int $numberOfIterations
     * @param int $numberOfSpecies
     * @throws \Exception
     */
    private function doPrint(array $organisms, int $worldWidth, int $worldHeight, int $numberOfIterations, int $numberOfSpecies): void
    {
        $this->ansi->eraseDisplay();

        $cells = array_fill(0, $worldHeight, array_fill(0, $worldWidth, 0));
        foreach ($organisms as $organism) {
            $cells[$organism->y][$organism->x] = $organism->species;
        }

        foreach ($cells as $row) {
            $currentType = reset($row);
            $this->ansi->color($this->color[$currentType]);
            $this->printRow($row, $currentType);
            $this->ansi->nostyle()->lf();
        }

        $currentIteration = $this->totalIterations - $numberOfIterations;
        $this->ansi->text("Iteration #$currentIteration")->lf();

        if ($numberOfSpecies > $this->colorsCount) {
            $this->ansi->text("Warning: not enough ASCII chars configured to cover all $numberOfSpecies species types!")->lf();
        }

        $this->ansi->e();
    }

    /**
     * @param array $row
     * @param int $currentType
     */
    private function printRow(array $row,int $currentType): void
    {
        foreach ($row as $cellType) {
            if ($currentType !== $cellType) {
                $this->ansi->nostyle()
                    ->color($this->color[$cellType]);
                $currentType = $cellType;
            }
            $this->ansi->text('  ');
        }
    }
}
