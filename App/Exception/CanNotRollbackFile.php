<?php


namespace Material4\Codegen\App\Exception;

class CanNotRollbackFile extends \Exception
{
    public function __construct($backupFilePath, $filePath)
    {
        parent::__construct(sprintf(
            'Can not rollback file "%s" contents from "%s" contents.',
            $filePath, $backupFilePath
        ), 500, null);
    }
}
