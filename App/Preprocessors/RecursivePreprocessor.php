<?php

namespace Codegenhub\App\Preprocessors;

use Jawira\CaseConverter\Convert;
use Codegenhub\App\PreProcessorInterface;

class RecursivePreprocessor implements PreProcessorInterface
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
        foreach ($items as $item) {
            if ($item['type'] === 'object') {
                $this->classes[$item['class']] = [
                    'properties' => $this->processClasses($item['properties']),
                    'type' => 'object',
                    'class' => $item['class'],
                ];
            }

            if ($item['type'] === 'array') {
                $this->classes[$item['class']] = [
                    'properties' => $this->processClasses($item['properties']),
                    'type' => 'object',
                    'class' => $item['class'],
                ];
            }
        }

        return array_values($this->classes);
    }

    public function processClasses(array $properties): array
    {
        foreach ($properties as $key => $property) {
            if ($property['type'] === 'object') {
                $className = (new Convert($key))->toPascal();
                $properties[$key]['type'] = $className;
                $props = array_merge([], $property['properties']);
                unset($properties[$key]['properties']);
                $this->classes[$className] = [
                    'properties' => $this->processClasses($props),
                    'type' => 'object',
                    'class' => $className,
                ];
            }
            if ($property['type'] === 'array' && $property['items']['type'] === 'object') {
                $className = (new Convert($key))->toPascal();
                $properties[$key]['isArray'] = true;
                $properties[$key]['type'] = $className;
                $props = array_merge([], $property['items']['properties']);
                unset($properties[$key]['items']);
                $this->classes[$className] = [
                    'properties' => $this->processClasses($props),
                    'type' => 'object',
                    'class' => $className,
                ];
            }

        }

        return $properties;
    }
}
