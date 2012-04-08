<?php
//mock ClassDispatcher class
namespace Utopia\Components\ClassDispatcher;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Template\Template;
use Utopia\Components\Workflow\ApiWorkflow;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Projects\Common\Controllers\HelloController;
use Utopia\Projects\Common\Controllers\DumpController;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testRender() {
        $expected = '<h1>normal html tags</h1> <b>bold text</b> <link rel="stylesheet" href="http://localhost:81/u3/css/?f=test1_css" type="text/css" media="screen"> <script type="text/javascript" src="http://localhost:81/u3/js/?f=test1_js"></script> <meta> api: module: arg: params: outputs:  hello how are you? there are 3 balls left there are 2 balls left there are 1 balls left ';
        $c = new HelloController('tests/phpunit', 'GET');
        $t = Template::summon()
            ->setController($c)
            ->render();

            echo $t;

        $this->assertEquals($expected, str_replace("\n"," ",$t) );
    }
}