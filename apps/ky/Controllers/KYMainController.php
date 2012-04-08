<?php
namespace Utopia\Projects\KY\Controllers;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class KYMainController extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET:    ~
#    val1: { type: 'text', info: 'val1', def: '1', req: true }
dependencies:
  GET: ~
#    modules:
#      module_name1: {url: 'ky/xxx/{params>yyy}', method: 'GET', params: '' }
#    api:
#      module_name1: {url: 'ky/xxx/{params>yyy}', method: 'GET', params: '' }
layout:
  templates:
    GET:    'ky.main'
  css:
    GET: ~
#      - 'apps/ky/static/css/reset-fonts-grids.css'
js:
    GET:
#      - 'apps/ky/static/js/jquery-1.6.1.min.js'
EOF;

    public function GET(){
        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}