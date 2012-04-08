<?php
/**
 * the centralized entry for everything
 **/
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\Console\Console;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Logger\Logger;


global $TIMER;
$TIMER = microtime(true);

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
date_default_timezone_set('Asia/Hong_Kong');

require_once dirname(__FILE__).'/lib/components/mallocworks/Loader/Autoload.php';
Autoload::summon();

//determine if it is running Commands or Controllers
if (isset($argc))
{
    $console = new Console();
    $console->run();
}
else
{
    //load autoload namespaces from configurationbundle
    $project = isset($_GET['_project'])? $_GET['_project']: 'common';
    $env = isset($_GET['_environment'])? $_GET['_environment']: 'phpunit';
    $bundle = ConfigurationBundle::summon();
    $bundle
        ->setDimensionFile('conf/dimensions.yml')
        ->setTargetDimensions(array(
          'property'=>$project,
          'environment'=>$env))
        ->setDeltaFile("apps/{$project}/deltas/delta.{$project}.yml");
    Autoload::summon()->includeNamespaces($bundle->get_value('autoload.namespaces', array()));

    //workflow dispatching
    $method = isset($_GET['_method'])? $_GET['_method']: $_SERVER['REQUEST_METHOD'];
    $uri = isset($_GET['_uri'])? $_GET['_uri']: trim($_SERVER['REQUEST_URI'], '/');
    Logger::summon()->log("project=$project | env=$env | uri=$uri | method=$method", ComponentRoot::LEVEL_DEBUG);
    $workflow = ClassDispatcher::summon()->dispatch($uri, $method);

    //overrides for workflow
    $overrides = array();
    if (isset($_GET['jscss'])){ $overrides['jscss'] = $_GET['jscss']; }
    if (isset($_GET['format'])){ $overrides['format'] = $_GET['format']; }

    if ($workflow instanceof BaseWorkflow) {
        echo $workflow->render($overrides);
    } else {
        Logger::summon()->log("cannot find workflow for uri '$uri' ($method)", ComponentRoot::LEVEL_ERROR);
    }
}