<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;

abstract class TorchBaseCommand extends BaseCommand
{
    /**
     * Run
     * 
     * @param array $params
     */
    abstract public function run(array $params);
}
