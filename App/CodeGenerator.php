<?php


namespace Codegenhub\App;

use Symfony\Component\Yaml\Yaml;
use Codegenhub\App\Model\Asset;
use Codegenhub\App\Model\MappingOptions;
use Codegenhub\App\Utils\Append;
use Codegenhub\App\Utils\SourceHelper;

class CodeGenerator
{
    /** @var InterpolatorInterface */
    private $interpolator;

    private $generatedCode = [];

    private $generatedJsonItems = [];

    /** @var SourceHelper */
    private $sourceHelper;

    /**
     * CodeGenerator constructor.
     * @param SourceHelper $templateHelper
     * @param InterpolatorInterface $interpolator
     */
    public function __construct(SourceHelper $templateHelper, InterpolatorInterface $interpolator)
    {
        $this->sourceHelper = $templateHelper;
        $this->interpolator = $interpolator;
    }

    /**
     * @param $contextItems
     * @throws \Exception
     */
    public function generate($contextItems)
    {
        $newFiles = [];

        foreach ($contextItems as &$contextItem) {
            $this->enrichItem($contextItem);
        }

        foreach ($this->sourceHelper->getTemplateMappings() as $mappingName => $options) {
            $skipConditionChecker = $this->buildSkipConditionChecker($options->getSkipCondition());
            $filePath = $this->getBasePath() . '/' . $options->getRelativePath();
            $trim = $options->getTrim();
            $assoc = $options->getAssoc();
            $trimNewLines = $options->getTrimNewLines();
            $render = $options->getRender();

            if ($options->getType() !== MappingOptions::TYPE_NEW_FILE) {
                $this->sourceHelper->backupOriginalFile($filePath);
            }

            foreach ($contextItems as $item) {
                if ($skipConditionChecker($item)) {
                    continue;
                }
                if ($options->getFileNameTemplate() !== null) {
                    $filePath = implode('/', [
                        $this->getBasePath(),
                        $options->getRelativePath(),
                        $this->interpolator->interpolate($options->getFileNameTemplate(), $item, [])
                    ]);
                    if ($options->getType() === MappingOptions::TYPE_NEW_FILE) {
                        $newFiles[] = $filePath;
                    }
                    if ($options->getType() !== MappingOptions::TYPE_NEW_FILE) {
                        $this->sourceHelper->backupOriginalFile($filePath);
                    }
                }
                $templateFilePath = $this->sourceHelper->getTemplateFolderBasePath() . '/' . $options->getTemplate();
                $renderedTemplate = $render
                    ? $this->renderTemplate(file_get_contents($templateFilePath), $item, $item === end($contextItems))
                    : file_get_contents($templateFilePath);
                switch ($options->getType()) {
                    case MappingOptions::TYPE_JSON:
                        $itemPaths = $this->parsePaths($options->getJsonPath(), $item);
                        $this->appendGeneratedJson($renderedTemplate, $filePath, $itemPaths, $assoc);
                        break;
                    case MappingOptions::TYPE_SECONDARY:
                        $this->generatedCode[$filePath] = Append::appendTextItem($this->generatedCode[$filePath], $mappingName, $renderedTemplate);
                        break;
                    default:
                        $key = $options->getType() === MappingOptions::TYPE_NEW_FILE ? $filePath : $filePath . $mappingName;
                        $this->appendGeneratedCode($renderedTemplate, $key, $trim, $trimNewLines, $options->getType() === MappingOptions::TYPE_NEW_FILE);
                        break;
                }
            }

            if ($options->getType() !== MappingOptions::TYPE_NEW_FILE) {
                $initialContents = file_exists($filePath) ? file_get_contents($filePath) : $this->generatedCode[$filePath];
                $contents = $this->applyGeneratedItems($filePath, $initialContents, $options->getType() === MappingOptions::TYPE_JSON, $mappingName);
                $this->sourceHelper->writeChanges($filePath, $contents);
            }
        }

        foreach ($newFiles as $filePath) {
            $this->sourceHelper->writeChanges($filePath, $this->generatedCode[$filePath]);
        }
        file_put_contents('new_files.txt', implode(';', $newFiles));
    }

    private function buildSkipConditionChecker(?string $skipCondition): callable
    {
        if ($skipCondition) {
            return function ($item) use ($skipCondition) {
                return trim($this->interpolator->interpolate($skipCondition, $item, [])) === 'skip';
            };
        } else {
            return function ($item) {
                return false;
            };
        }
    }


    /**
     * @throws Exception\CanNotRollbackFile
     */
    public function rollback(Asset $asset)
    {
        $items = Yaml::parseFile($asset->getItemsPath());
        foreach ($asset->getPreprocessors() as $preprocessor) {
            $items = $preprocessor->process($items);
        }

        foreach ($this->sourceHelper->getTemplateMappings() as $mappingName => $mappingOptions) {
//            if ($mappingOptions->getType() == MappingOptions::TYPE_NEW_FILE) {
//                continue;
//            }

            $filePath = $this->sourceHelper->getTemplateFolderBasePath() . '/' . ltrim($mappingOptions->getRelativePath(), '/');
            $paths = [$filePath];
            if ($mappingOptions->getFileNameTemplate() !== null) {
                foreach ($items as $item) {
                    $this->enrichItem($item);
                    $paths[] = $this->sourceHelper->getTemplateFolderBasePath() . '/' .
                        trim($mappingOptions->getRelativePath(), '/') . '/' .
                        trim($this->interpolator->interpolate($mappingOptions->getFileNameTemplate(), $item, []), '/');
                }
            }


            $paths = array_unique($paths);
            foreach ($paths as $filePath) {
                try {
                    $this->sourceHelper->rollback($filePath);
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        $newFiles = explode(';', @file_get_contents('new_files.txt')) ?? [];
        foreach ($newFiles as $file) {
            try {
                $file = realpath($file);
                @unlink($file);
            } catch (\Throwable $e) {
                continue;
            }
        }
        @unlink('new_files.txt');

        foreach (array_reverse($asset->getPostProcessors()) as $postprocessor) {
            $postprocessor->precondition();
            foreach ($items as $item) {
                $postprocessor->revert($item);
            }
        }
    }

    /**
     * @param $template
     * @param $contextItem
     * @param bool $isLastItem
     * @return mixed
     */
    private function renderTemplate($template, $contextItem, $isLastItem = false)
    {
        if (is_string($contextItem)) {
            var_dump($contextItem);
            exit();
        }
        return $this->interpolator->interpolate($template, $contextItem, [
            'is_last_item' => $isLastItem
        ]);
    }

    /**
     * Replace path tokens(if any exist) with corresponding item values
     *
     * Example:
     *  $path = 'a.{b}.{c}'
     *  $item = [
     *    'b' => ['b1'],
     *    'c' => ['c1', 'c2'],
     *  ]
     *
     *  return = [
     *    'a.b1.c1',
     *    'a.b1.c2'
     *  ]
     *
     * @param $path
     * @param $item
     * @return array
     */
    private function parsePaths($path, $item)
    {
        if (!preg_match_all("/{(\S+?)}/", $path, $pathProperties)) {
            return [$path];
        }
        $pathProperties = $pathProperties[1];

        $pathReplacements = [[]];
        foreach ($pathProperties as $pathProperty) {
            if (!isset($item[$pathProperty])) {
                // paths cannot be parsed for this item
                return [];
            }
            $pathValues = $item[$pathProperty];
            $pathReplacements = $this->addPathReplacements($pathReplacements, $pathProperty, $pathValues);
        }

        return array_map(function ($replacements) use ($path) {
            $replaceKeys = array_keys($replacements);
            $replaceValues = array_values($replacements);
            return str_replace($replaceKeys, $replaceValues, $path);
        }, $pathReplacements);
    }

    private function addPathReplacements($currentReplacements, $key, $values)
    {
        $replacements = [];
        foreach ($values as $value) {
            $newReplacements = array_map(function ($replacement) use ($key, $value) {
                $replacementKey = '{' . $key . '}';
                $replacement[$replacementKey] = $value;
                return $replacement;
            }, $currentReplacements);
            $replacements = array_merge($replacements, $newReplacements);
        }

        return $replacements;
    }

    private function appendGeneratedJson($item, $filePath, $paths = [], $assoc = false)
    {
        if (!isset($this->generatedJsonItems[$filePath])) {
            $this->generatedJsonItems[$filePath] = [];
        }

        $item = json_decode($item, true);
        foreach ($paths as $path) {
            if (!isset($this->generatedJsonItems[$filePath][$path])) {
                $this->generatedJsonItems[$filePath][$path] = [];
            }
            if ($assoc) {
                $this->generatedJsonItems[$filePath][$path] = array_merge($this->generatedJsonItems[$filePath][$path], $item);
            } else {
                $this->generatedJsonItems[$filePath][$path][] = $item;
            }
        }
    }

    private function appendGeneratedCode($item, $filePath, $trim = true, $trimNewLines = false, $once = false)
    {
        if (!isset($this->generatedCode[$filePath])) {
            $this->generatedCode[$filePath] = '';
        }

        if ($trimNewLines) {
            $item = trim($item, PHP_EOL);
        }

        if (trim($item) != '') {
            if (!$once) {
                $this->generatedCode[$filePath] .= ($trim ? trim($item) : $item) . PHP_EOL;
            } else {
                $this->generatedCode[$filePath] = ($trim ? trim($item) : $item) . PHP_EOL;
            }
        }
    }

    private function applyGeneratedItems($filePath, $content, $isJson, $mappingName)
    {
        if ($isJson) {
            $object = json_decode($content, true);
            foreach ($this->generatedJsonItems[$filePath] ?? [] as $path => $jsonItems) {
                Append::appendJsonItemsToPath(
                    $object,
                    explode('.', $path),
                    $jsonItems
                );
            }
            return json_encode($object, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            return Append::appendTextItem($content, $mappingName, $this->generatedCode[$filePath . $mappingName] ?? '');
        }
    }

    private function getBasePath()
    {
        return $this->sourceHelper->getTemplateFolderBasePath();
    }

    /**
     * @param $contextItem
     */
    private function enrichItem(&$contextItem): void
    {
        foreach ($this->sourceHelper->getAdditionalFields() as $field => $template) {
            $contextItem[$field] = $this->renderTemplate($template, $contextItem, false);
        }
    }
}
