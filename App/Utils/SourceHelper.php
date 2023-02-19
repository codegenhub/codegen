<?php


namespace Codegenhub\App\Utils;

use Codegenhub\App\Exception\CanNotRollbackFile;
use Symfony\Component\Yaml\Yaml;
use Codegenhub\App\Model\Asset;
use Codegenhub\App\Model\MappingOptions;

class SourceHelper
{
    private $savedFiles = [];

    private $asset;

    /**
     * TemplatesHelper constructor.
     * @param string $asset
     * @param string|null $baseDir
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @param $filePath
     * @throws CanNotRollbackFile
     */
    public function rollback($filePath)
    {
        $backupFilePath = realpath($filePath . '.orig');
        $filePath = realpath($filePath);
        if (file_exists($filePath) && file_exists($backupFilePath)) {
            copy($backupFilePath, $filePath);
        } else {
            throw new CanNotRollbackFile($backupFilePath, $filePath);
        }

        if (file_exists($backupFilePath)) {
            unlink($backupFilePath);
        }
    }

    public function backupOriginalFile($filePath)
    {
        $filePath = realpath($filePath);
        if (!isset($this->savedFiles[$filePath])) {
            $this->savedFiles[$filePath] = true;
            if ($filePath && is_file($filePath)) {
                copy($filePath, $filePath . '.orig');
            }
        }
    }

    public function getTemplateMappings()
    {
        return $this->asset->getSettings()->getMappings();
    }

    public function getAdditionalFields()
    {
        return $this->asset->getSettings()->getFields();
    }

    public function getTemplateFolderBasePath()
    {
        return $this->asset->getTemplatesPath();
    }

    public function writeChanges($filePath, $contents)
    {
        if (!file_exists($filePath)) {
            shell_exec("mkdir -p $filePath");
            shell_exec("rm -rf $filePath");
        }
        file_put_contents($filePath, $contents);
    }
}
