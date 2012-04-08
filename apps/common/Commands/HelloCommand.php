<?php
namespace Utopia\Projects\Common\Commands;

use Utopia\Components\Console\BaseCommand;

class HelloCommand extends BaseCommand
{
    protected $name = 'common.hello';
    protected $command_details = <<<EOF
aliases: []
description: 'Just to say hi'
help: >
  A demo command
  usage: ./run <comment>common.hello</comment> <comment>name</comment>
spec:
  name: { req: true, info: 'your name'}
EOF;

    public function execute($input, $output) {
        $name = $input->getArgument('name');
        $output->writeln("hello, $name!");
    }
}