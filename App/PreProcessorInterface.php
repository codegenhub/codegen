<?php

namespace Material4\Codegen\App;
;

interface PreProcessorInterface
{
    public function process(array $items): array;
}
