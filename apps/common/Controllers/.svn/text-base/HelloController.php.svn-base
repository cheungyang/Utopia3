<?php
namespace Utopia\Projects\Common\Controllers;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

class HelloController extends BaseController
{
    protected $_config = <<<EOF
validation:
  GET: ~
dependencies:
  GET:
    apis:
      hello.shout: {url:'hello/shout/{args>name}', method: 'GET', params: '{"a":"A","b":"B"}'}
    modules:
      hello.header: {url:'hello/header/{args>name}', method: 'GET', params: ''}
layout:
  templates:
    GET: 'test1'
  css:
    GET: 'test1_css'
  js:
    GET: 'test1_js'
EOF;

    public function GET(){
        $this->data->{"outputs>greetings"} = "hello world!";
        return ComponentRoot::OKAY;
    }

    public function POST(){return ComponentRoot::ERROR;}
    public function PUT(){return ComponentRoot::ERROR;}
    public function DELETE(){return ComponentRoot::ERROR;}
}