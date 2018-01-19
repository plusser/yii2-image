<?php 

namespace image\models;

use Yii;
use Exception;

class DefaultImage extends Image
{

    protected $fileName;
    protected $modelClass;
    protected $modelPrimaryKey;

    public function save($runValidation = TRUE, $attributeNames = NULL)
    {
        throw new Exception('Модель класса `' . static::className() . '` не сохраняется.');
    }

    public function delete()
    {
        throw new Exception('Модель класса `' . static::className() . '` не удаляется.');
    }

    public function getImageFileName()
    {
        return Yii::getAlias($this->module->defaultImage);
    }

    public function init()
    {
        parent::init();

        $this->fileName = basename($this->imageFileName);
        $this->modelClass = static::className();
        $this->modelPrimaryKey = NULL;
    }

}
