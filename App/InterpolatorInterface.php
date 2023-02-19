<?php


namespace Codegenhub\App;

interface InterpolatorInterface
{

    /**
     * PhpInterpolator constructor.
     * @param string $template
     * @param array $item
     * @param array $additional
     */
    public function interpolate(string $template, array $item, array $additional);
}
