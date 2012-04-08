<?php
//running urls
//    http://kindriver.tpcity.corp.yahoo.com:9998/u3/run.php?_project=ctr_viewer&_environment=dev&_uri=ctr_viewer

namespace Utopia\Projects\CtrViewer\Controllers;

use Utopia\Components\ClassDispatcher\ClassDispatcher;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class CtrViewerController extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET:
    route_default: { type: 'text', info: 'default route string', def: 'tests.api.hello', req: true }
dependencies:
  GET:
    modules:
      selector: {url: 'ctr_viewer/module/route_selector/{params>route_default}', method: 'GET', params: '' }
      configuration: {url: 'ctr_viewer/module/route_configuration/{params>route_default}', method: 'GET', params: '' }
      execution: {url: 'ctr_viewer/module/route_execution', method: 'POST', params: '{"url":"tests/api/hello","method":"GET","param_str":"abc"}'}
layout:
  templates:
    GET: 'ctr_viewer.main'
  css:
    GET:
      - 'apps/ctr_viewer/static/css/reset-fonts-grids.css'
      - 'apps/ctr_viewer/static/css/ctr_viewer.css'
  js:
    GET:
      - 'apps/ctr_viewer/static/js/jquery-1.6.1.min.js'
      - 'apps/ctr_viewer/static/js/jquery.corner.js'
      - 'apps/ctr_viewer/static/js/utopia3_base.js'
      - 'apps/ctr_viewer/static/js/ctr_viewer.js'
EOF;

    public function GET(){
        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}