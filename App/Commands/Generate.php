<?php


namespace Codegenhub\App\Commands;


use Exception;
use Codegenhub\App\CodeGenerator;
use Codegenhub\App\StringInterpolator;
use Codegenhub\App\Utils\SourceHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Generate extends AssetAware
{
    public function getDescription(): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'code:generate';
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $asset = $this->getAsset($input);
        $codeGen = new CodeGenerator(new SourceHelper($asset), new StringInterpolator());
        $items = Yaml::parseFile($asset->getItemsPath());

        foreach ($asset->getPreprocessors() as $preprocessor) {
            $items = $preprocessor->process($items);
        }
        $codeGen->generate($items);
        foreach ($asset->getPostProcessors() as $postProcessor) {
            $postProcessor->precondition();
            foreach ($items as $item) {
                $postProcessor->process($item);
            }
        }

        return 0;
    }
}
