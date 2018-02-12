<?php 

namespace image\behaviors;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\validators\Validator;
use image\models\Image;
use image\models\DefaultImage;
use image\traits\getModuleTrait;

class ImageBehavior extends Behavior
{

    use getModuleTrait;

    protected $primaryKey = [];
    protected $imageList = [];

    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function attach($owner)
    {
        parent::attach($owner);

        $owner->validators[] = Validator::createValidator('safe', $this->owner, ['image', 'images']);
    }

    public function beforeUpdate($event)
    {
        if($event->isValid){
            foreach($this->owner->primaryKey() as $item){
                if(($newValue = $this->owner->getAttribute($item)) != ($oldValue = $this->owner->getOldAttribute($item))){
                    $this->owner->setAttribute($item, $oldValue);
                    $this->primaryKey[$item] = $newValue;
                }
            }

            if(!empty($this->primaryKey)){
                $this->imageList = $this->owner->images;
                $this->owner->setAttributes($this->primaryKey);
            }
        }
    }

    public function afterUpdate($event)
    {
        if(!empty($this->primaryKey)){
            foreach($this->imageList as $model){
                $model->modelPrimaryKey = serialize($this->owner->getPrimaryKey(TRUE));
                $model->save();
            }
        }
    }

    public function beforeDelete($event)
    {
        if($event->isValid){
            foreach($this->owner->images as $model){
                $model->delete();
            }
        }
    }

    public function addImage($fileName, $isMain = FALSE, $name = NULL)
    {
        $model = new Image;

        $model->fileName = $fileName;
        $model->modelClass = get_class($this->owner);
        $model->modelPrimaryKey = serialize($this->owner->getPrimaryKey(TRUE));
        $model->isMain = $isMain;
        $model->name = $name;

        return $model->save();
    }

    public function getImage()
    {
        return is_object($image = Image::findOne([
            'isMain' => TRUE,
            'modelClass' => get_class($this->owner),
            'modelPrimaryKey' => serialize($this->owner->getPrimaryKey(TRUE)),
        ])) ? $image : new DefaultImage;
    }

    public function setImage($fileName)
    {
        if(!empty($fileName) AND strval($image = $this->image) != $fileName){
            if(!($image instanceof DefaultImage)){
                $image->delete();
            }

            $this->addImage($fileName);
        }
    }

    public function getImages()
    {
        return Image::find()->where([
            'modelClass' => get_class($this->owner),
            'modelPrimaryKey' => serialize($this->owner->getPrimaryKey(TRUE)),
        ])->all();
    }

    public function setImages($fileNameList)
    {
        if(!empty($fileNameList)){
            array_walk($images = $this->images, function(&$item, $index){
                $item = strval($item);
            });

            $diff = array_diff($fileNameList, $images);

            if(!empty($diff)){
                $this->deleteImages();

                foreach($fileNameList as $fileName){
                    $this->addImage($fileName);
                }
            }
        }
    }

    public function deleteImages()
    {
        foreach($this->images as $model){
            $model->delete();
        }
    }

}
