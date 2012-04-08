<?php
require_once dirname(__FILE__).'/../../Core/DataObject.php';
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Core\DataObject;

class DataParserTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testBasic(){
        $parser = DataParser::summon();

        $filename = dirname(__FILE__).'/test1.yml';
        $arr = array('a'=>array('b'=>'hello'));
        $o = $parser->asDataObj($filename);
        $this->assertEquals($arr, $o->get(''));
    }

    public function testYaml(){
        $parser = DataParser::summon();

        $filename = dirname(__FILE__).'/test1.yml';
        $this->assertEquals(array('a'=>array('b'=>'hello')), $parser->asArray($filename));

        $yaml = <<<YAML
a:
  b: 'hello2'
YAML;
        $this->assertEquals(array('a'=>array('b'=>'hello2')), $parser->asArray($yaml));

        $arr = array('a'=>array('b'=>'hello2'));
        $this->assertEquals("a:\n  b: hello2\n", $parser->asYaml($arr));

        $arr = array('a'=>array('b'=>'hello'));
        $o = $parser->asDataObj($filename);
        $this->assertEquals($arr, $o->get(''));

        $controllerdetails = <<<EOF
accepts:
  base: 'welcome'
  required: ~
  methods: ['GET']
description: 'Getting started to pages'
spec:
  get:  ~
  post: ~
external:
  css:
    ver: '0.0.1'
    inc:
      - 'http:\/\/l.yimg.com\/mq\/i\/sports\/css\/page_api_viewer_1_0_0.css'
  js:
    ver: '0.0.1'
    inc:
      - 'http:\/\/code.jquery.com\/jquery-1.4.2.min.js'
      -
        alert('hello');
  tpl: 'lib/Core/Templates/welcome.tpl'
EOF;
        $array = $parser->asArray($controllerdetails);
        $this->assertFalse($array == array());
    }

    public function testXml(){
        $parser = DataParser::summon();

        //normal case
        $filename = dirname(__FILE__).'/test1.xml';
        $array = $parser->asArray($filename);
        //print_r($array);
        $this->assertEquals('Yahoo! News: The Newsroom', $array['rss']['channel']['title']);
        $xml = $parser->asXml($array);
        //echo $xml;

        //flatten/offset/limit/targetchild case
        $args = array(
            'flatten' => false,
            'targetchild' => 'item',
            'offset' => 0,
            'limit' => 10,
        );
        $array = $parser->asArray($filename,'xml', $args);
        $this->assertEquals($args['limit'], count($array['rss']['@children']['channel']['@children']['item']));


    }

    public function testJson(){
        $parser = DataParser::summon();

        $filename = dirname(__FILE__).'/test1.json';
        $array = $parser->asArray($filename);
        $this->assertEquals(array('a'=>'A','b'=>'B'), $array);
        $json = $parser->asJson($array);
        $this->assertEquals('{"a":"A","b":"B"}', str_replace(array(' ',"\n"),array('',''),$json));
    }

    public function testDataobj(){
        $parser = DataParser::summon();

        $array = array('a'=>'A','b'=>'B');
        $dataobject = $parser->asDataobj($array);
        $this->assertEquals('A', $dataobject->get('a'));

        $array_object = new DataObject($array);
        $dataarray = $parser->asArray($array_object);
        $this->assertEquals('B', $dataarray['b']);
    }
}