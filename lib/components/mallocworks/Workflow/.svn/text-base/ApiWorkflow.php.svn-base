<?php
namespace Utopia\Components\Workflow;

use Utopia\Components\Core\DataObject;

use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Workflow\BaseWorkflow;

class ApiWorkflow extends BaseWorkflow
{
    public function render($overrides=array()) {
        parent::render($overrides);

        //execute and get response
        $result_set = new DataObject();
        $controller = $this->getController();

        if (ComponentRoot::OKAY === $controller->validateParams($controller->getMethod())
            && ComponentRoot::OKAY === $controller->execute($controller->getMethod())
        ) {
            $result_set->results = $controller->getResultSet();
        } else {
            $result_set->results = array();
        }

        //add error messages and inputs
        $result_set->inputs = $controller->getInputs();
        $result_set->{"inputs>url"} = $controller->getUrl();
        $result_set->errors = $controller->getErrors();
        GLOBAL $TIMER;
        $result_set->elapsed = microtime(true) - $TIMER;

        //output formatting
        $format = isset($overrides['format'])? $overrides['format']
            : ( isset($_GET['format'])? $_GET['format']
            : ConfigurationBundle::summon()->{"workflow>defaults>format"});

        $formatted_string = DataParser::summon()->{'as'.ucfirst($format)}($result_set, 'dataobj');
        return $formatted_string;
    }
}