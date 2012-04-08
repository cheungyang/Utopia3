<?php
namespace Utopia\Components\Controller;

interface IController{

    public function GET();
    public function POST();
    public function DELETE();
    public function PUT();
}