<?php


namespace Codegenhub\App\Model;


class Settings
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getItemsPath(string $basePath): string
    {
        return realpath($basePath . '/' . ($this->config['items'] ?? ''));
    }

    public function getPreprocessors(): array
    {
        try {
            return array_map(function ($preprocessor) {
                $class = $preprocessor['class'];
                $settings = $preprocessor['settings'] ?? [];
                return new $class($settings);
            }, $this->config['preprocessors'] ?? []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getPostProcessors(): array
    {
        try {
            return array_map(function ($postprocessor) {
                $class = $postprocessor['class'];
                $settings = $postprocessor['settings'] ?? [];
                return new $class($settings);
            }, $this->config['postprocessors'] ?? []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @return MappingOptions[]
     */
    public function getMappings(): array
    {
        return array_map(function ($item) {
            return new MappingOptions($item);
        }, $this->config['mappings'] ?? []);
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->config['fields'] ?? [];
    }
}
