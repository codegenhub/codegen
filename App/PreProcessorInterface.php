<?php

namespace Codegenhub\App;
;

interface PreProcessorInterface
{
    public function process(array $items): array;
}
