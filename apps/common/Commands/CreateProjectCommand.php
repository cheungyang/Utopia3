<?php
namespace Utopia\Projects\Common\Commands;

use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Helper\HelperFactory;
use Utopia\Components\Console\BaseCommand;

class CreateProjectCommand extends BaseCommand
{
    protected $name = 'create.project';
    protected $command_details = <<<EOF
aliases: []
description: 'Create configs and folder structure'
help: >
  Create configs and folder structure for new projects
  usage: ./run <comment>create.project ctr_viewer CtrViewer main Main</comment>
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

        $output->write('creating folders');
        $done = HelperFactory::summon()->file_makeDirs(array(
            "apps/$ps",
            "apps/$ps/Controllers",
            "apps/$ps/Controllers/tests",
            "apps/$ps/deltas",
            "apps/$ps/static",
            "apps/$ps/static/img",
            "apps/$ps/static/js",
            "apps/$ps/static/css",
            "apps/$ps/templates"
        ));
        if ($done){
            $output->writeln('[done]');
        } else {
            $output->writeln('[failed]');
            return ComponentRoot::ERROR;
        }

        $output->write('moving file to target project');
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/deltas/delta.proj_small.yml" => "apps/$ps/deltas/delta.$ps.yml",
            "apps/_template/deltas/routes.proj_small.yml" => "apps/$ps/deltas/routes.$ps.yml"
        ), ".yml");
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/Controllers/ProjCapCtrCapController.php" => "apps/$ps/Controllers/{$pc}{$cc}Controller.php",
            "apps/_template/Controllers/tests/ProjCapCtrCapControllerTest.php" => "apps/$ps/Controllers/tests/{$pc}{$cc}ControllerTest.php"
        ), "php");
        HelperFactory::summon()->file_copyFiles(array(
            "apps/_template/templates/proj_small.ctr_small.tpl" => "apps/$ps/templates/{$ps}.{$cs}.tpl"
        ), "tpl");
        HelperFactory::summon()->file_copyFiles(array("apps/_template/static" => "apps/$ps/static"), "");
        $output->writeln('[done]');

        $output->write('replacing context');
        $html = HelperFactory::summon()->file_filterContents(
            array("apps/$ps/deltas", "apps/$ps/Controllers", "apps/$ps/templates"),
            array(
                '/###PROJ_SMALL###/' => $ps,
                '/###PROJ_CAP###/'   => $pc,
                '/###CTR_SMALL###/'  => $cs,
                '/###CTR_CAP###/'    => $cc
            )
        );
        $output->writeln('[done]');
        $output->writeln('----all done---');
    }
}