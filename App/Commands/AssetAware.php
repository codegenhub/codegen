<?php

namespace Codegenhub\App\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;
use Codegenhub\App\Model\Asset;
use Codegenhub\App\Model\Config;
use Symfony\Component\Console\Command\Command;

abstract class AssetAware extends Command
{
    private $indexedAssets;

    public function configure()
    {
        parent::configure();

        $this->addArgument('asset', InputArgument::REQUIRED,
            'Asset name'
        );
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getAsset(InputInterface $input): Asset
    {
        $configPath = realpath(__DIR__ . '/../../../Codegen/');
        $config = Yaml::parseFile($configPath . '/config.yaml');
        $config = new Config($config);
        $basePath = realpath($configPath . '/' . $config->getBasePath());
        $this->indexedAssets = [];
        foreach ($config->getAssets() as $asset) {
            $asset->setBasePath($basePath);
            $this->indexedAssets[$asset->getName()] = $asset;
        }
        $assetName = $input->getArgument('asset');
        if (!isset($this->indexedAssets[$assetName])) {
            throw new \Exception("Asset ${assetName} is not supported, use following: "
                . implode(', ', array_keys($this->indexedAssets))
            );
        }

        return $this->indexedAssets[$assetName];
    }
}
