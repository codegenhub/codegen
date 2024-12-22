<?php

namespace Codegenhub\App\Commands;

use Codegenhub\App\CodeGenerator;
use Codegenhub\App\Exception\CanNotRollbackFile;
use Codegenhub\App\StringInterpolator;
use Codegenhub\App\Utils\SourceHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Rollback extends AssetAware
{
    public function getDescription(): string
    {
        return '';
    }

    public function getName()
    {
        return 'code:rollback';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $asset = $this->getAsset($input);
        $codeGen = new CodeGenerator(new SourceHelper($asset), new StringInterpolator());
        try {
            $codeGen->rollback($asset);
            echo sprintf('Generated code rolled back to previous state.');
        } catch (CanNotRollbackFile $e) {
            echo sprintf('Unable to rollback code: "%s".', $e->getMessage());
        }

        return 0;
    }
}
