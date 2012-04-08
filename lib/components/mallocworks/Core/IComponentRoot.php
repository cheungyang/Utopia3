<?php
namespace Utopia\Components\Core;

interface IComponentRoot{

    public static function isSingleton();
    public function initialize($mixed=false);

    //public function getConfigurations();
    //public function getDependencies();
    //public function getErrors();
}