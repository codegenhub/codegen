<?php

namespace Codegenhub\App\Postprocessors;

use Codegenhub\App\PostProcessorInterface;
use Codegenhub\App\StringInterpolator;

class CommandPostProcessor implements PostProcessorInterface
{
    /**
     * @param $settings
     */
    public function __construct(private $settings)
    {
    }

    public function precondition(): void
    {
        if (isset($this->settings['precondition'])) {
            shell_exec($this->settings['precondition']);
        }
    }

    public function process($item): void
    {
        if (isset($this->settings['process'])) {
            shell_exec($this->getEvaluated($this->settings['command'], $item));
        }
    }

    public function revert($item): void
    {
        if (isset($this->settings['revert'])) {
            shell_exec($this->getEvaluated($this->settings['revert'], $item));
        }
    }

    private function getEvaluated($template, $item): string
    {
        $interpolator = new StringInterpolator();
        return $interpolator->interpolate($template, $item, []);
    }
}
