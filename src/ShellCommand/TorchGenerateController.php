<?php

namespace Torch\ShellCommand;

use Torch\ShellCommand;

class TorchGenerateController extends ShellCommand
{
    /**
     * Render command string
     * 
     * @return string
     */
    public function renderCommandString() : string
    {
        $controller = $this->getArgument('controller');
        $resource = $this->getOption('--resource');
        $dbTable = $this->getOption('--db-table');
        return self::$baseCommandString .':controller '
            . $controller 
            . ($resource ? ' --resource' : '')
            . ($dbTable ? ' --db-table '. $dbTable : '');
    }
}
