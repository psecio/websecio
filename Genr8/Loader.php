<?php

namespace Genr8;

class Loader
{
    public function __construct()
    {
        spl_autoload_register(array($this,'load'));
    }
    private function load($className)
    {
        $p = explode('\\',$className);
        $file = implode('/',$p).'.php';
        
        if (is_file($file)) {
            include_once $file;
            return true;
        } else {
            return false;
        }
    }
}