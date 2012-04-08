<?php
namespace Utopia\Projects\Common\Controllers;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Controller\BaseController;

/**
 * Just a dump controller to test out BaseController
 * @author ycheung
 */
class DumpController extends BaseController
{
    public function GET(){return $this;}
    public function POST(){return $this;}
    public function PUT(){return $this;}
    public function DELETE(){return $this;}
}