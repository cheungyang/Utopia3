<?php
namespace Utopia\Components\Console;

use Utopia\Components\Autoloader\Autoload;
use Utopia\Components\Core\DataObject;
use Utopia\Components\DataParser\DataParser;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Input\OutputInterface;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;

abstract class BaseCommand extends \Symfony\Components\Console\Command\Command
{
    protected $name = '__NO_NAME__';
    protected $command_details = array();
    private $_data;

    public function __construct() {
        parent::__construct();
        $this->_data = new DataObject();
    }

    /**
     * inherent from \Symfony\Components\Console\Command\Command
     */
    protected function configure() {
        $this->setName($this->getCmdName())
            ->setDescription($this->getCmdDescription())
            ->setDefinition($this->getCmdDefinition())
            ->setHelp($this->getCmdHelp())
            ->setAliases($this->getCmdAliases());
    }

    protected function getCmdName() {
        return $this->name;
    }

    protected function getCmdDescription() {
        return $this->_getCmdInfo('description');
    }

    protected function getCmdHelp() {
        return $this->_getCmdInfo('info');
    }

    protected function getCmdAliases() {
        return $this->_getCmdInfo('aliases');
    }

    protected function getCmdDefinition() {
        //parse spec
        $this->_loadCommandDetails();
        $this->_data->setPointer('spec');

        //handle first level arguments only
        $definitions = array();
        foreach($this->_data as $key => $req) {
            if ($req['req'] && isset($req['def'])) {
                $definitions[] = new InputArgument($key, InputArgument::OPTIONAL, isset($req['info'])?$req['info']:'', $req['def']);
            } elseif ($req['req']){
                $definitions[] = new InputArgument($key, InputArgument::REQUIRED, isset($req['info'])?$req['info']:'');
            } else {
                $definitions[] = new InputOption($key, substr($key, 0, 2), InputOption::PARAMETER_OPTIONAL, isset($req['info'])?$req['info']:'', isset($req['def'])? $req['def']: null);
            }
        }

        //return
        return $definitions;
    }

    protected function getDefinitionDefault($name) {
        //parse spec
        $this->_loadCommandDetails();
        return $this->_data->get('spec'.SEP.$name.SEP."def", false);
    }

    private function _loadCommandDetails() {
        $parser = DataParser::summon();
        $this->_data = $parser->asDataObj($this->command_details);
    }

    private function _getCmdInfo($name) {
        if (!isset($this->_data->{$name})) {
            //parse spec
            $this->_loadCommandDetails();
        }
        return $this->_data->{$name};
    }
}