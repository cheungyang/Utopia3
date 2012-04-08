<?php
namespace Utopia\Projects\Common\Commands;

use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Helper\HelperFactory;
use Utopia\Components\Console\BaseCommand;

class CreateModuleCommand extends BaseCommand
{
    protected $name = 'create.module';
    protected $command_details = <<<EOF
aliases: []
description: 'Create a module under an existing project'
help: >
  Create a module under an existing project
  usage: ./run <comment>create.module ctr_viewer CtrViewer module Module</comment>
spec:
  proj_small: { req: true, info: 'project name, small form'}
  proj_cap: { req: true, info: 'project name, camelized'}
  ctr_small: { req: true, info: 'first controller name, small form'}
  ctr_cap: { req: true, info: 'first controller name, camelized'}
EOF;

    public function execute($input, $output) {
        $ps = $input->getArgument('proj_small');
        $pc = $input->getArgument('proj_cap');
        $cs = $input->getArgument('ctr_small');
        $cc = $input->getArgument('ctr_cap');
        $replacement = array(
                '/###PROJ_SMALL###/' => $ps,
                '/###PROJ_CAP###/'   => $pc,
                '/###CTR_SMALL###/'  => $cs,
                '/###CTR_CAP###/'    => $cc
        );

        if (file_exists("apps/$ps/Controllers/{$pc}{$cc}Controller.php")) {
            $output->writeln('module already exist, command terminated');
            return ComponentRoot::ERROR;
        }

        $output->write('moving file to target project');
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/Controllers/ProjCapCtrCapController.php" => "apps/$ps/Controllers/{$pc}{$cc}Controller.php",
            "apps/_template/Controllers/tests/ProjCapCtrCapControllerTest.php" => "apps/$ps/Controllers/tests/{$pc}{$cc}ControllerTest.php"
        ), "php");
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/templates/proj_small.ctr_small.tpl" => "apps/$ps/templates/{$ps}.{$cs}.tpl"
        ), "tpl");
        $output->writeln('[done]');

        $output->write('adding route');
        $route = file_get_contents("apps/_template/deltas/routes.proj_small.yml");
        $route = "\n\n\n". preg_replace(array_keys($replacement), array_values($replacement), $route);
        file_put_contents("apps/$ps/deltas/routes.$ps.yml", $route, FILE_APPEND);
        $output->writeln('[done]');

        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/Controllers/ProjCapCtrCapController.php" => "apps/$ps/Controllers/{$pc}{$cc}Controller.php",
            "apps/_template/Controllers/tests/ProjCapCtrCapControllerTest.php" => "apps/$ps/Controllers/tests/{$pc}{$cc}ControllerTest.php"
        ), "php");
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/templates/proj_small.ctr_small.tpl" => "apps/$ps/templates/{$ps}.{$cs}.tpl"
        ), "tpl");
        $output->writeln('[done]');

        $output->write('replacing context');
        $html = HelperFactory::summon()->file_filterContents(
            array(
                "apps/$ps/Controllers/{$pc}{$cc}Controller.php",
                "apps/$ps/Controllers/tests/{$pc}{$cc}ControllerTest.php",
                "apps/$ps/templates/{$ps}.{$cs}.tpl"
            ), $replacement
        );
        $output->writeln('[done]');
        $output->writeln('----all done---');
    }
}