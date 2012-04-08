<?php
namespace Utopia\Projects\###PROJ_CAP###\Controllers;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class ###PROJ_CAP######CTR_CAP###Controller extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET:    ~
#    val1: { type: 'text', info: 'val1', def: '1', req: true }
dependencies:
  GET: ~
#    modules:
#      module_name1: {url: '###PROJ_SMALL###/xxx/{params>yyy}', method: 'GET', params: '' }
#    api:
#      module_name1: {url: '###PROJ_SMALL###/xxx/{params>yyy}', method: 'GET', params: '' }
layout:
  templates:
    GET:    '###PROJ_SMALL###.###CTR_SMALL###'
  css:
    GET: ~
#      - 'apps/###PROJ_SMALL###/static/css/reset-fonts-grids.css'
js:
    GET:
#      - 'apps/###PROJ_SMALL###/static/js/jquery-1.6.1.min.js'
EOF;

    public function GET(){
        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}