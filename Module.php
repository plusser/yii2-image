<?php 

namespace image;

use Yii;

class Module extends \yii\base\Module
{

    public static $instance;

    public $imagesPath = '@webroot/images';
    public $storePath = '@webroot/store';
    public $defaultImage = '@webroot/images/default.png';
    public $storeUrl = '/store';
    public $quality = 90;

    public function init()
    {
        parent::init();

        static::$instance = $this;
    }

    protected function getImagesRealPath()
    {
        return Yii::getAlias($this->imagesPath);
    }

    protected function getStoreRealPath()
    {
        return Yii::getAlias($this->storePath);
    }

}
