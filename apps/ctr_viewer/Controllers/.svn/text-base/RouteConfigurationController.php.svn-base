<?php
namespace Utopia\Projects\CtrViewer\Controllers;

use Utopia\Components\Validator\ValidationObject;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Validator\Validator;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class RouteConfigurationController extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET: ~
dependencies:
  GET: ~
layout:
  templates:
    GET: 'ctr_viewer.route.configuration'
  css:
    GET:
      - 'apps/ctr_viewer/static/css/reset-fonts-grids.css'
      - 'apps/ctr_viewer/static/css/ctr_viewer.css'
  js:
    GET:
      - 'apps/ctr_viewer/static/js/jquery-1.6.1.min.js'
      - 'apps/ctr_viewer/static/js/jquery.corner.js'
      - 'apps/ctr_viewer/static/js/utopia3_base.js'
      - 'apps/ctr_viewer/static/js/ctr_viewer.route.configuration.js'
EOF;

    public function GET(){
        $route = ClassDispatcher::summon()->getRoute($this->getInputs('args>route_name'));
        if (empty($route)) {
            Logger::summon()->log("cannot find route '".$this->getInputs('args>route_name')."'", ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
        }
        $this->data->{"outputs>route"} = $route;
        $vo = new ValidationObject($route['required']);
        $this->data->{"outputs>htmltags>args"} = $vo->toHtmlTags('args');
        //echo "<pre>". print_r($this->data->get(''), true)."</pre>";

        try{
            $controller = new $route['controller']('whatever_url', 'GET');
            $this->data->{"outputs>htmltags>params"} = $controller->getValidationObject()->toHtmlTags('params');
        } catch(Exception $e) {
            Logger::summon()->log($e.getMessage(), ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
        }

        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}