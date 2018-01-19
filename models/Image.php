<?php 

namespace image\models;

use Yii;
use Imagick;
use Exception;
use image\traits\getModuleTrait;

class Image extends \yii\db\ActiveRecord
{

    use getModuleTrait;

    public static function tableName()
    {
        return 'image';
    }

    public function rules()
    {
        return [
            [['fileName', 'modelClass', 'modelPrimaryKey', 'isMain'], 'required'],
            [$uniqueModelKey = ['fileName', 'modelClass', 'modelPrimaryKey'], 'unique', 'targetAttribute' => $uniqueModelKey],
            [['fileName', 'modelClass', 'modelPrimaryKey', 'name'], 'string', 'max' => 255],
            [['isMain'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fileName' => 'Файл',
            'modelClass' => 'Класс модели',
            'modelPrimaryKey' => 'Первичный ключ модели',
            'isMain' => 'Главная',
            'name' => 'Название',
        ];
    }

    public function setMain()
    {
        $this->isMain = TRUE;

        return $this->save();
    }

    public function beforeSave($insert)
    {
        if($result = parent::beforeSave($insert)){
            if(!$insert AND (($newValue = $this->getAttribute('modelPrimaryKey')) != ($oldValue = $this->getOldAttribute('modelPrimaryKey')))){
                $this->setAttribute('modelPrimaryKey', $oldValue);
                $this->deleteDirectory(dirname($this->storeFileName));
                $this->setAttribute('modelPrimaryKey', $newValue);
            }

            if(!static::find()->where($condition = [
                'modelClass' => $this->modelClass,
                'modelPrimaryKey' => $this->modelPrimaryKey,
            ])->count()){
                $this->isMain = TRUE;
            }elseif($this->isMain){
                static::getDb()->createCommand()->update(static::tableName(), [
                    'isMain' => FALSE,
                ], $condition)->execute();
            }
        }

        return $result;
    }

    public function beforeDelete()
    {
        if($result = parent::beforeDelete()){
            $this->deleteDirectory(dirname($this->storeFileName));
        }

        return $result;
    }

    public function getStoreFileName($size = NULL)
    {
        return $this->module->storeRealPath . DIRECTORY_SEPARATOR . md5($this->modelClass . $this->modelPrimaryKey) . DIRECTORY_SEPARATOR . md5($this->id . md5($size)) . '.' . pathinfo($this->fileName, PATHINFO_EXTENSION);
    }

    public function getImageFileName()
    {
        return preg_match('/^[http|https]:\/\/(.*)/si', $this->fileName) ? $this->fileName : $this->module->imagesRealPath . DIRECTORY_SEPARATOR . $this->fileName;
    }

    public function getUrl($size = NULL, $remake = FALSE)
    {
        if(!file_exists($fileName = $this->getStoreFileName($size)) OR $remake){
            $this->makeImage($fileName, $size);
        }

        return rtrim($this->module->storeUrl, '/') . str_replace($this->module->storeRealPath, '', $fileName);
    }

    protected function makeImage($fileName, $size)
    {
        if(!file_exists($dirName = dirname($storeOriginalFileName = $this->storeFileName))){
            mkdir($dirName, 0775, TRUE);
            chmod($dirName, 0775);
        }

        file_put_contents($storeOriginalFileName, file_get_contents($this->imageFileName));

        if(!is_null($size)){
            $img = new Imagick($storeOriginalFileName);

            extract($this->parseSize($size));

            $img->thumbnailImage($width, $height);

            $img->stripImage();
            $img->setImageCompressionQuality($this->module->quality);

            $img->writeImage($fileName);

            $img->clear();
        }
    }

    protected function parseSize($size)
    {
        if(!preg_match('/^(?P<width>\d+)?x(?P<height>\d+)?$/si', $size, $M)){
            throw new Exception('Задан неправильный формат размера `' . $size . '`. Пример: `200x150`, `200x`, `x150`.');
        }

        return [
            'width' => (!isset($M['width']) OR empty($M['width'])) ? NULL : $M['width'],
            'height' => (!isset($M['height']) OR empty($M['height'])) ? NULL : $M['height'],
        ];
    }

    protected function deleteDirectory($dirName)
    {
        if(file_exists($dirName) AND is_dir($dirName)){
            foreach(glob($dirName . DIRECTORY_SEPARATOR . '*.*') as $fileName){
                unlink($fileName);
            }

            rmdir($dirName);
        }
    }

}
