<?php 

use yii\db\Migration;

class m180119_180846_image extends Migration
{

    protected $tableName = 'image';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->null(),
            'fileName' => $this->string()->notNull(),
            'modelClass' => $this->string()->notNull(),
            'modelPrimaryKey' => $this->string()->notNull(),
            'isMain' => $this->boolean()->notNull()->defaultValue(FALSE),
        ]);

        $this->createIndex('idx_' . $this->tableName . '_fileName_modelClass_modelPrimaryKey', $this->tableName, [
            'fileName',
            'modelClass',
            'modelPrimaryKey',
        ], TRUE);
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

}
