<?php

namespace Torch\ShellCommand;

use Torch\ShellCommand;

class TorchGenerateModel extends ShellCommand
{
    /**
     * Render command string
     * 
     * @return string
     */
    public function renderCommandString() : string
    {
        $model = $this->getArgument('model');
        $dbTable = $this->getOption('--db-table');
        return self::$baseCommandString .':model '
            . $model 
            . ($dbTable ? ' --db-table '. $dbTable : '');
    }
}
