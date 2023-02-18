<?php

namespace Material4\Codegen\App;

interface PostProcessorInterface
{
    public function precondition(): void;

    public function process($item): void;

    public function revert($item): void;
}
