<?php


namespace Codegenhub\App\Model;


class Config
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Asset[]
     */
    public function getAssets(): array
    {
        $assets = [];
        foreach ($this->config['assets'] ?? [] as $assetName => $assetConfigPath) {
            $assets[] = new Asset($assetName, $assetConfigPath);
        }

        return $assets;
    }

    public function getBasePath(): string
    {
        return $this->config['base_path'] ?? '';
    }
}
