<?php 

namespace image\traits;

use Exception;
use image\Module;

trait getModuleTrait
{

    protected function getModule()
    {
        if(!is_object(Module::$instance)){
            throw new Exception('Необходимо активировать модуль ' . Module::className());
        }

        return Module::$instance;
    }

}
