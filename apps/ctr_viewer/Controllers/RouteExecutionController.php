<?php
namespace Utopia\Projects\CtrViewer\Controllers;

use Utopia\Components\Logger\Logger;

use Utopia\Components\Workflow\BaseWorkflow;

use Utopia\Components\Validator\ValidationObject;

use Utopia\Components\Validator\Validator;

use Utopia\Components\ClassDispatcher\ClassDispatcher;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class RouteExecutionController extends BaseController
{
    protected $_config = <<<EOF
validation:
  POST:
    url:       { type: 'text', info: 'url of of the API', def: '', req: true }
    method:    { type: ['GET','POST','PUT','DELETE'], info: 'API method', def: 'GET', req: true }
    param_str: { type: 'text', info: 'input parameters as string', def: '', req: false }
dependencies:
  POST:
layout:
  templates:
    POST: 'ctr_viewer.route.execution'
  css:
    POST:
      - 'apps/ctr_viewer/static/css/reset-fonts-grids.css'
      - 'apps/ctr_viewer/static/css/ctr_viewer.css'
  js:
    POST:
      - 'apps/ctr_viewer/static/js/jquery-1.6.1.min.js'
      - 'apps/ctr_viewer/static/js/jquery.corner.js'
      - 'apps/ctr_viewer/static/js/utopia3_base.js'
      - 'apps/ctr_viewer/static/js/ctr_viewer.route.execution.js'
EOF;

    public function GET(){
        return ComponentRoot::ERROR;
    }

    public function POST(){
        $method = strtoupper($this->getInputs('params>method'));
        $url = $this->getInputs('params>url');
        $params = $this->getInputs('params>params');

        //FIXME:
//        [inputs_raw] => Array
//        (
//            [params] => Array
//                (
//                    [url] => ctr_viewer/
//                    [method] => GET
//                    [params] => params[route_default]=module.route.configuration
//                )
//
//        )

        //echo "<pre>". print_r($this->data->get(''), true)."</pre>";

        $wf = ClassDispatcher::summon()->dispatch($url, $method);
        if ($wf instanceof BaseWorkflow) {
            $overrides[$method] = $params;
            $this->data->{"outputs>baseuri"} = 'http://kindriver.tpcity.corp.yahoo.com:9998/u3/run.php?_project=ctr_viewer&_environment=dev&_uri=';
            $this->data->{"outputs>url"} = $url;
            $this->data->{"outputs>method"} = $method;
            $this->data->{"outputs>render"} = $wf->render($overrides);
            return ComponentRoot::OKAY;
        } else {
            return ComponentRoot::ERROR;
        }
    }

    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}