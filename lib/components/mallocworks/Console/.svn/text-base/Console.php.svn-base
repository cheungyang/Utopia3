<?php
namespace Utopia\Components\Console;

use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Loader;
use Utopia\Components\Logger\Logger;

class Console extends \Symfony\Components\Console\Application
{
    /**
     * constructor
     *
     * @param string           $projname
     * @param string           $version
     */
    public function __construct($projname="unknown project", $version="x.x.x") {
        parent::__construct($projname, $version);
        $namespaces = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
            ->{"console>namespaces"};
        $this->registerNamespaces($namespaces);
    }

    /**
     * register set of commands that can be run
     *
     * @param arary $namespaces
     */
    public function registerNamespaces(array $namespaces) {
        foreach ($namespaces as $namespace) {
            $this->registerNamespace($namespace);
        }
        return $this;
    }

    /**
     * register a namespace of controllers that the console can be run
     *
     * @param string $namespace
     */
    public function registerNamespace($namespace) {
        $classtrings = Loader::summon()->getNamespaceClasses($namespace);
        $classes = array();
        foreach ($classtrings as $classstr) {
            try {
                if (class_exists($classstr)) {
                    $class = new $classstr();
                    if (is_subclass_of($class, 'Utopia\Components\Console\BaseCommand')) {
                        $classes[$classstr] = $class;
                    }
                }
            } catch (\Exception $e) {
                Logger::summon()->log("error loading class '$classstr' - ". $e->getMessage(), ComponentRoot::LEVEL_ERROR);
            }
        }
        $this->addCommands($classes);
        return $this;
    }
}