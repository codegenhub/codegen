<?php

namespace Material4\Codegen\App\Preprocessors;

use Material4\Codegen\App\PreProcessorInterface;

class ImagesPreprocessor implements PreProcessorInterface
{
    private $classes = [];

    /**
     * @param $settings
     */
    public function __construct(private $settings)
    {
    }

    public function process(array $items): array
    {
        foreach ($items as $i => $item) {
            $local = "tmp/p$i.jpg";
            file_put_contents($local, file_get_contents($item['img']));
            $items[$i]['img'] = "p$i";
        }

        return $items;
    }
}
