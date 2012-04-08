<?php
namespace Utopia\Projects\CtrViewer\Controllers;

use Utopia\Components\ClassDispatcher\ClassDispatcher;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class RouteSelectorController extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET: ~
dependencies:
  GET: ~
layout:
  templates:
    GET: 'ctr_viewer.route.selector'
  css:
    GET:
      - 'apps/ctr_viewer/static/css/reset-fonts-grids.css'
      - 'apps/ctr_viewer/static/css/ctr_viewer.css'
  js:
    GET:
      - 'apps/ctr_viewer/static/js/jquery-1.6.1.min.js'
      - 'apps/ctr_viewer/static/js/jquery.corner.js'
      - 'apps/ctr_viewer/static/js/utopia3_base.js'
      - 'apps/ctr_viewer/static/js/ctr_viewer.route.selector.js'
EOF;

    public function GET(){
        $this->data->{"outputs>default_value"} = $this->getArguments('_extra') == null? 'NULL': $this->getArguments('_extra');
        $this->data->{"outputs>routes"} = ClassDispatcher::summon()->getRouteNames();
        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}