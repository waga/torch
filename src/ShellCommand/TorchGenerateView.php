<?php

namespace Torch\ShellCommand;

use Torch\ShellCommand;

class TorchGenerateView extends ShellCommand
{
    /**
     * Render command string
     * 
     * @return string
     */
    public function renderCommandString() : string
    {
        $view = $this->getArgument('view');
        $template = $this->getOption('--template');
        $dbTable = $this->getOption('--db-table');
        return self::$baseCommandString .':view '
            . $view 
            . ($template ? ' --template '. $template : '')
            . ($dbTable ? ' --db-table '. $dbTable : '');
    }
}
