<?php


namespace Codegenhub\App\Model;


use Codegenhub\App\PreProcessorInterface;
use Symfony\Component\Yaml\Yaml;
use Codegenhub\App\PostProcessorInterface;

class Asset
{
    /** @var string */
    private $name;

    /** @var string */
    private $configPath;

    /** @var string */
    private $basePath;

    /** @var Settings */
    private $settings;

    public function __construct(string $name, string $configRelativePath)
    {

        $this->name = $name;
        $this->configPath = $configRelativePath;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array<PreProcessorInterface>
     */
    public function getPreprocessors(): array
    {
        return $this->getSettings()->getPreprocessors();
    }

    /**
     * @return array<PostProcessorInterface>
     */
    public function getPostProcessors(): array
    {
        return $this->getSettings()->getPostProcessors();
    }

    /**
     * @return string
     */
    public function getSettingsConfigPath(): string
    {
        if (!isset($this->basePath)) {
            throw new \Exception('Не задан basePath');
        }
        return realpath($this->basePath . '/' . $this->configPath);
    }

    /**
     * @return string
     */
    public function getTemplatesPath(): string
    {
        return dirname($this->getSettingsConfigPath());
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getItemsPath(): string
    {
        return $this->getSettings()->getItemsPath($this->basePath);
    }

    public function getSettings(): Settings
    {
        if ($this->settings === null) {
            $this->settings = new Settings(Yaml::parseFile($this->getSettingsConfigPath()));
        }

        return $this->settings;
    }
}
