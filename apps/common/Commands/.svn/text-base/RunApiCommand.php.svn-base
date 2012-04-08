<?php
namespace Utopia\Projects\Common\Commands;

use Utopia\Components\Core\ComponentRoot;

use Utopia\Components\Console\BaseCommand;
use Utopia\Components\ClassDispatcher\ClassDispatcher;

class RunApiCommand extends BaseCommand
{
    protected $name = 'controller';
    protected $command_details = <<<EOF
aliases: []
description: 'Execute controllers in command line'
help: >
  Execute controllers in command line
  usage: ./run <comment>controller</comment>
spec:
  url: { req: true, info: 'url'}
  method: { req: true, info: 'method'}
EOF;

    public function execute($input, $output) {
        $url = $input->getArgument('url');
        $method = $input->getArgument('method');

        $workflow = ClassDispatcher::summon()->dispatch($url, $method);
        if ($workflow !== ComponentRoot::ERROR) {
            echo $workflow->render();
        }
    }
}