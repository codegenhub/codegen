<?php

namespace Codegenhub\App\Commands;

use Codegenhub\App\CodeGenerator;
use Codegenhub\App\Exception\CanNotRollbackFile;
use Codegenhub\App\Model\Asset;
use Codegenhub\App\StringInterpolator;
use Codegenhub\App\Utils\SourceHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Live extends Generate
{
    const DELAY = 100;

    private $helper;

    private $watch = [];

    /** @var Asset */
    private $asset;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'code:live';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->asset = $this->getAsset($input);
        $this->helper = new SourceHelper($this->asset);
        $this->watch[$this->asset->getItemsPath()] = 0;
        $this->updateFiles();

        while (true) {
            usleep(static::DELAY * 1e3);

            foreach ($this->watch as $file => $lastTime) {
                $file = realpath($file);
                if (!file_exists($file)) {
                    unset($this->watch[$file]);
                    continue;
                }

                $newTime = filemtime($file);
                if ($newTime > $lastTime) {
                    $this->updateFiles();

                    try {
                        $this->rollback();
                        parent::execute($input, $output);
                    } catch (\Throwable $e) {
                        echo $e->getMessage() . PHP_EOL;
                    }

                    $this->watch[$file] = $newTime;
                }
            }
        }
    }

    private function rollback()
    {
        $codeGen = new CodeGenerator($this->helper, new StringInterpolator());
        try {
            $codeGen->rollback($this->asset);
            echo sprintf('Generated code rolled back to previous state.');
        } catch (CanNotRollbackFile $e) {
            echo sprintf('Unable to rollback code: "%s".', $e->getMessage());
        }
    }

    private function updateFiles()
    {
        $basePath = $this->helper->getTemplateFolderBasePath();

        $settingsConfigPath = $this->asset->getSettingsConfigPath();
        if (!isset($this->watch[$settingsConfigPath])) {
            $this->watch[$settingsConfigPath] = 0;
        }

        foreach ($this->helper->getTemplateMappings() as $options) {
            $filePath = $basePath . '/' . ltrim($options->getTemplate());
            if (!isset($this->watch[$filePath])) {
                $this->watch[$filePath] = 0;
            }
        }
    }
}
