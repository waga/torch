<?php

namespace Torch\ShellCommand;

use Torch\ShellCommand;

class TorchGenerateRoute extends ShellCommand
{
    /**
     * Render command string
     * 
     * @return string
     */
    public function renderCommandString() : string
    {
        $url = $this->getArgument('url');
        $handler = $this->getArgument('handler');
        $as = $this->getOption('--as');
        $method = $this->getOption('--method');
        return self::$baseCommandString .':route '
            . $url 
            . ' '. $handler
            . ($as ? ' --as '. $as : '')
            . ($method ? ' --method '. $method : '');
    }
}
